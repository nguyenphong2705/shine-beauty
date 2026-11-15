<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\RegistrationOtpService;
use App\Services\SmsGatewayService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OtpAndOrderCancellationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_otp_is_hashed_in_redis_and_never_stored_as_plain_text(): void
    {
        $phone = '0987654321';

        $res = $this->postJson('/api/auth/otp/send', [
            'phone' => $phone,
            'provider' => 'log',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('ok', true)
            ->assertJsonPath('code', 'OTP_SENT')
            ->assertJsonPath('expires_in', 180);

        $plainOtp = $res->json('dev_otp_preview');
        $this->assertNotEmpty($plainOtp);

        $storedRecord = Cache::get(RegistrationOtpService::recordKey($phone));
        $this->assertIsArray($storedRecord);
        $this->assertArrayHasKey('otp_hash', $storedRecord);
        $this->assertNotEquals($plainOtp, $storedRecord['otp_hash']);
        $this->assertStringStartsWith('$2y$', $storedRecord['otp_hash']);
        $this->assertTrue(Hash::check($plainOtp, $storedRecord['otp_hash']));
    }

    public function test_rate_limiting_enforces_60s_cooldown_and_max_3_sends_per_15_minutes(): void
    {
        $phone = '0987111222';

        // Lần gửi 1: Thành công
        $this->postJson('/api/auth/otp/send', ['phone' => $phone, 'provider' => 'log'])
            ->assertStatus(200);

        // Gửi lại ngay lập tức (< 60s): Bị chặn bởi Cooldown 60 giây
        $this->postJson('/api/auth/otp/send', ['phone' => $phone, 'provider' => 'log'])
            ->assertStatus(429)
            ->assertJsonPath('code', 'OTP_COOLDOWN_ACTIVE');

        // Xóa cooldown để mô phỏng đã qua 60 giây cho lần gửi 2 và 3
        Cache::forget(RegistrationOtpService::cooldownKey($phone));
        $this->postJson('/api/auth/otp/send', ['phone' => $phone, 'provider' => 'log'])
            ->assertStatus(200)
            ->assertJsonPath('remaining_sends_in_window', 1);

        Cache::forget(RegistrationOtpService::cooldownKey($phone));
        $this->postJson('/api/auth/otp/send', ['phone' => $phone, 'provider' => 'log'])
            ->assertStatus(200)
            ->assertJsonPath('remaining_sends_in_window', 0);

        // Lần gửi thứ 4 trong vòng 15 phút: Bị chặn bởi giới hạn 3 lần / 15 phút
        Cache::forget(RegistrationOtpService::cooldownKey($phone));
        $this->postJson('/api/auth/otp/send', ['phone' => $phone, 'provider' => 'log'])
            ->assertStatus(429)
            ->assertJsonPath('code', 'OTP_RATE_LIMIT_EXCEEDED');
    }

    public function test_otp_auto_invalidates_after_5_failed_attempts_or_after_successful_verification(): void
    {
        $phone = '0987333444';

        $sendRes = $this->postJson('/api/auth/otp/send', ['phone' => $phone, 'provider' => 'log'])
            ->assertStatus(200);
        $correctOtp = $sendRes->json('dev_otp_preview');
        $wrongOtp = $correctOtp === '000000' ? '999999' : '000000';

        // Nhập sai 4 lần đầu -> báo còn số lần thử
        for ($i = 1; $i <= 4; $i++) {
            $this->postJson('/api/auth/otp/verify', ['phone' => $phone, 'otp' => $wrongOtp])
                ->assertStatus(422)
                ->assertJsonPath('code', 'OTP_MISMATCH')
                ->assertJsonPath('remaining_attempts', 5 - $i);
        }

        // Lần nhập sai thứ 5 -> tự động vô hiệu hóa ngay lập tức
        $this->postJson('/api/auth/otp/verify', ['phone' => $phone, 'otp' => $wrongOtp])
            ->assertStatus(429)
            ->assertJsonPath('code', 'OTP_MAX_ATTEMPTS_EXCEEDED');

        $this->assertNull(Cache::get(RegistrationOtpService::recordKey($phone)));

        // Kể cả nhập đúng mã cũ sau khi đã sai 5 lần cũng không còn hiệu lực
        $this->postJson('/api/auth/otp/verify', ['phone' => $phone, 'otp' => $correctOtp])
            ->assertStatus(422)
            ->assertJsonPath('code', 'OTP_EXPIRED_OR_NOT_FOUND');

        // Gửi mã mới và xác thực đúng -> mã tự hủy ngay sau lần verify thành công đầu tiên
        Cache::forget(RegistrationOtpService::cooldownKey($phone));
        $sendRes2 = $this->postJson('/api/auth/otp/send', ['phone' => $phone, 'provider' => 'log'])
            ->assertStatus(200);
        $otp2 = $sendRes2->json('dev_otp_preview');

        $verifyRes = $this->postJson('/api/auth/otp/verify', ['phone' => $phone, 'otp' => $otp2])
            ->assertStatus(200)
            ->assertJsonPath('ok', true)
            ->assertJsonPath('code', 'OTP_VERIFIED');

        // Record OTP đã bị xóa khỏi Redis ngay sau khi verify thành công
        $this->assertNull(Cache::get(RegistrationOtpService::recordKey($phone)));

        // Dùng verification_token để hoàn tất đăng ký tài khoản
        $token = $verifyRes->json('verification_token');
        $this->postJson('/api/auth/register', [
            'name' => 'Khách Hàng OTP',
            'username' => 'khachhang_otp_' . random_int(1000, 9999),
            'email' => 'otp_' . random_int(1000, 9999) . '@example.com',
            'phone' => $phone,
            'password' => 'Secret@123',
            'verification_token' => $token,
        ])->assertStatus(201)
          ->assertJsonPath('code', 'REGISTER_SUCCESS');
    }

    public function test_sms_gateway_service_integrates_esms_zalo_zns_and_twilio(): void
    {
        Http::fake([
            'rest.esms.vn/*' => Http::response(['CodeResult' => '100', 'SMSID' => 'ESMS-12345'], 200),
            'business.openapi.zalo.me/*' => Http::response(['error' => 0, 'data' => ['msg_id' => 'ZNS-67890']], 200),
            'api.twilio.com/*' => Http::response(['sid' => 'SM987654321'], 201),
        ]);

        config([
            'services.sms.esms.api_key' => 'test_esms_key',
            'services.sms.esms.secret_key' => 'test_esms_secret',
            'services.sms.zalo_zns.access_token' => 'test_zalo_token',
            'services.sms.zalo_zns.template_id' => 'tpl_123',
            'services.sms.twilio.account_sid' => 'AC12345',
            'services.sms.twilio.auth_token' => 'tw_secret',
            'services.sms.twilio.from' => '+15005550006',
        ]);

        $gateway = app(SmsGatewayService::class);

        $esmsRes = $gateway->sendViaEsms('0909123456', '123456');
        $this->assertTrue($esmsRes['success']);
        $this->assertEquals('ESMS-12345', $esmsRes['message_id']);

        $znsRes = $gateway->sendViaZaloZns('0909123456', '123456');
        $this->assertTrue($znsRes['success']);
        $this->assertEquals('ZNS-67890', $znsRes['message_id']);

        $twilioRes = $gateway->sendViaTwilio('0909123456', '123456');
        $this->assertTrue($twilioRes['success']);
        $this->assertEquals('SM987654321', $twilioRes['message_id']);
    }

    public function test_customer_can_only_cancel_order_when_logged_in_with_matching_owner_phone(): void
    {
        $ownerUser = User::create([
            'name' => 'Chính Chủ Đơn Hàng',
            'username' => 'chinhchu_' . random_int(1000, 9999),
            'email' => 'chinhchu_' . random_int(1000, 9999) . '@example.com',
            'phone' => '0909555666',
            'password' => Hash::make('Password@123'),
            'is_admin' => false,
            'role' => 'customer',
        ]);

        $otherUser = User::create([
            'name' => 'Người Dùng Khác SĐT',
            'username' => 'nguoikhac_' . random_int(1000, 9999),
            'email' => 'nguoikhac_' . random_int(1000, 9999) . '@example.com',
            'phone' => '0909777888',
            'password' => Hash::make('Password@123'),
            'is_admin' => false,
            'role' => 'customer',
        ]);

        $category = Category::first() ?? Category::create(['name' => 'Test Cat', 'slug' => 'test-cat']);
        $brand = Brand::first() ?? Brand::create(['name' => 'Test Brand', 'origin' => 'Pháp']);
        $product = Product::create([
            'sku' => 'TEST-CANCEL-01',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Sản phẩm test hủy đơn',
            'slug' => 'san-pham-test-huy-don-' . random_int(1000, 9999),
            'price' => 300000,
            'stock' => 10,
            'images' => [Product::FALLBACK_IMAGE],
            'description' => 'Mô tả sản phẩm test hủy đơn hàng',
            'ingredients' => 'Aqua, Glycerin',
            'skin_type' => 'Mọi loại da',
            'volume' => '50ml',
        ]);

        $order = Order::create([
            'user_id' => $ownerUser->id,
            'customer_name' => $ownerUser->name,
            'phone' => '0909555666',
            'address' => '123 Nguyễn Huệ, Quận 1, TP.HCM',
            'payment_method' => 'COD',
            'payment_status' => 'UNPAID',
            'status' => 'PENDING',
            'order_status' => 'PENDING',
            'total_amount' => 600000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 300000,
        ]);

        // 1. Chưa đăng nhập -> Không hủy được (401)
        $this->postJson(route('customer.orders.cancel', $order))
            ->assertStatus(401)
            ->assertJsonPath('code', 'UNAUTHENTICATED');

        // 2. Đăng nhập tài khoản khác SĐT chính chủ -> Bị từ chối (403 PHONE_MISMATCH_FORBIDDEN)
        $this->actingAs($otherUser)
            ->postJson(route('customer.orders.cancel', $order))
            ->assertStatus(403)
            ->assertJsonPath('code', 'PHONE_MISMATCH_FORBIDDEN');

        $this->assertEquals('PENDING', $order->fresh()->status);
        $this->assertEquals(10, $product->fresh()->stock);

        // 3. Đăng nhập đúng tài khoản có SĐT chính chủ (0909555666) -> Hủy đơn thành công & hoàn kho (+2 = 12)
        $this->actingAs($ownerUser)
            ->postJson(route('customer.orders.cancel', $order))
            ->assertStatus(200)
            ->assertJsonPath('ok', true)
            ->assertJsonPath('code', 'ORDER_CANCELLED');

        $this->assertEquals('CANCELLED', $order->fresh()->status);
        $this->assertEquals(12, $product->fresh()->stock);
    }
}
