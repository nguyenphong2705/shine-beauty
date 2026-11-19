<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BankTransferPaymentTest extends TestCase
{
    use DatabaseTransactions;

    private string $webhookToken = 'test_sepay_webhook_secret_2026';
    private string $bankAccount = '1029384756';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.sepay.webhook_token' => $this->webhookToken,
            'services.sepay.match_prefix' => 'DH',
            'services.vietqr.bank_id' => 'VCB',
            'services.vietqr.bank_account' => $this->bankAccount,
            'services.vietqr.bank_account_name' => 'SHINE BEAUTY COSMETICS',
            'services.vietqr.payment_timeout_minutes' => 15,
        ]);
    }

    private function createTestOrder(array $overrides = []): Order
    {
        $order = Order::query()->create(array_merge([
            'customer_name' => 'Nguyễn Thị Khách Hàng',
            'phone' => '0909123456',
            'address' => '123 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh',
            'payment_method' => 'BANK_TRANSFER',
            'payment_status' => 'PENDING_PAYMENT',
            'total_amount' => 550000,
            'status' => 'PENDING',
            'order_status' => 'PENDING',
        ], $overrides));

        app(\App\Services\PaymentService::class)->initializeOrderPayment($order);

        return $order->fresh(['payment']);
    }

    /**
     * 0. Tạo đơn hàng qua POST /api/orders tính tổng tiền từ Database, không tin giá gửi từ Frontend.
     */
    public function test_create_order_calculates_total_strictly_from_database_product_price(): void
    {
        $category = Category::query()->first() ?? Category::query()->create([
            'name' => 'Chăm Sóc Da Test',
            'slug' => 'cham-soc-da-test-' . uniqid(),
            'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03',
        ]);
        $brand = Brand::query()->first() ?? Brand::query()->create([
            'name' => 'La Roche-Posay Test',
            'origin' => 'Pháp',
            'logo' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03',
        ]);

        $product = Product::query()->create([
            'sku' => 'TEST-SKU-' . uniqid(),
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Serum B5 Test',
            'slug' => 'serum-b5-test-' . uniqid(),
            'price' => 300000,
            'sale_price' => 260000,
            'stock' => 20,
            'images' => ['https://images.unsplash.com/photo-1556228720-195a672e8a03'],
            'description' => 'Mô tả test',
            'ingredients' => 'Vitamin B5',
            'skin_type' => 'Mọi loại da',
            'volume' => '30ml',
        ]);

        // Gửi kèm price=1₫ giả mạo từ client -> Backend phải bỏ qua và tính đúng 2 * 260.000 = 520.000₫ + 15.000₫ ship nội thành = 535.000₫ (vì < 900.000₫)
        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Trần Thanh Tuyền',
            'phone' => '0912345678',
            'address' => '10 Lê Lợi, Quận 1, TP. Hồ Chí Minh',
            'province' => 'TP. Hồ Chí Minh',
            'district' => 'Quận 1',
            'payment_method' => 'BANK_TRANSFER',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => 1,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_amount', 535000)
            ->assertJsonPath('data.payment_status', 'PENDING_PAYMENT');
    }

    /**
     * 1. Chuyển khoản đúng số tiền -> Cập nhật PAID và đơn hàng sang CONFIRMED.
     */
    public function test_case_1_exact_amount_transfer_updates_payment_paid_and_order_confirmed(): void
    {
        $order = $this->createTestOrder(['total_amount' => 550000]);

        $response = $this->withHeaders([
            'Authorization' => 'Apikey ' . $this->webhookToken,
        ])->postJson('/api/webhooks/sepay', [
            'id' => 900001,
            'gateway' => 'Vietcombank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'accountNumber' => $this->bankAccount,
            'code' => $order->order_code,
            'content' => "Chuyen khoan don hang {$order->order_code}",
            'transferType' => 'in',
            'transferAmount' => 550000,
            'referenceCode' => 'FT2631900001',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', PaymentTransaction::STATUS_MATCHED_PAID)
            ->assertJsonPath('payment_status', Payment::STATUS_PAID);

        $order->refresh();
        $this->assertSame('PAID', $order->payment_status);
        $this->assertSame('CONFIRMED', $order->order_status);
        $this->assertSame('CONFIRMED', $order->status);
        $this->assertSame('FT2631900001', $order->transaction_code);
    }

    /**
     * 2. Chuyển khoản thiếu tiền -> UNDERPAID, không xác nhận PAID.
     */
    public function test_case_2_underpaid_transfer_does_not_confirm_order(): void
    {
        $order = $this->createTestOrder(['total_amount' => 550000]);

        $response = $this->withHeaders([
            'Authorization' => 'Apikey ' . $this->webhookToken,
        ])->postJson('/api/webhooks/sepay', [
            'id' => 900002,
            'gateway' => 'Vietcombank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'accountNumber' => $this->bankAccount,
            'code' => $order->order_code,
            'content' => $order->order_code,
            'transferType' => 'in',
            'transferAmount' => 300000,
            'referenceCode' => 'FT2631900002',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', PaymentTransaction::STATUS_UNDERPAID);

        $order->refresh();
        $this->assertSame('UNDERPAID', $order->payment_status);
        $this->assertSame('PENDING', $order->order_status);
    }

    /**
     * 3. Chuyển khoản thừa tiền -> OVERPAID, không tự động xác nhận nếu dữ liệu không khớp chính xác.
     */
    public function test_case_3_overpaid_transfer_flags_overpaid_for_reconciliation(): void
    {
        $order = $this->createTestOrder(['total_amount' => 550000]);

        $response = $this->withHeaders([
            'Authorization' => 'Apikey ' . $this->webhookToken,
        ])->postJson('/api/webhooks/sepay', [
            'id' => 900003,
            'gateway' => 'Vietcombank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'accountNumber' => $this->bankAccount,
            'code' => $order->order_code,
            'content' => $order->order_code,
            'transferType' => 'in',
            'transferAmount' => 700000,
            'referenceCode' => 'FT2631900003',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', PaymentTransaction::STATUS_OVERPAID);

        $order->refresh();
        $this->assertSame('OVERPAID', $order->payment_status);
        $this->assertSame('PENDING', $order->order_status);
    }

    /**
     * 4. Nội dung chuyển khoản sai -> UNMATCHED_ORDER.
     */
    public function test_case_4_wrong_transfer_content_records_unmatched_order(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Apikey ' . $this->webhookToken,
        ])->postJson('/api/webhooks/sepay', [
            'id' => 900004,
            'gateway' => 'Vietcombank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'accountNumber' => $this->bankAccount,
            'code' => '',
            'content' => 'NGUYEN VAN A CHUYEN TIEN MUA MY PHAM',
            'transferType' => 'in',
            'transferAmount' => 550000,
            'referenceCode' => 'FT2631900004',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', PaymentTransaction::STATUS_UNMATCHED_ORDER);

        $this->assertDatabaseHas('payment_transactions', [
            'provider_transaction_id' => '900004',
            'status' => PaymentTransaction::STATUS_UNMATCHED_ORDER,
        ]);
    }

    /**
     * 5. Giao dịch gửi Webhook nhiều lần (Idempotency).
     */
    public function test_case_5_duplicate_webhook_is_idempotent(): void
    {
        $order = $this->createTestOrder(['total_amount' => 550000]);
        $payload = [
            'id' => 900005,
            'gateway' => 'Vietcombank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'accountNumber' => $this->bankAccount,
            'code' => $order->order_code,
            'content' => $order->order_code,
            'transferType' => 'in',
            'transferAmount' => 550000,
            'referenceCode' => 'FT2631900005',
        ];

        $first = $this->withHeaders(['Authorization' => 'Apikey ' . $this->webhookToken])
            ->postJson('/api/webhooks/sepay', $payload);
        $first->assertStatus(200)->assertJsonPath('duplicate', false);

        $second = $this->withHeaders(['Authorization' => 'Apikey ' . $this->webhookToken])
            ->postJson('/api/webhooks/sepay', $payload);
        $second->assertStatus(200)->assertJsonPath('duplicate', true);

        $this->assertSame(
            1,
            PaymentTransaction::query()->where('provider_transaction_id', '900005')->count()
        );
    }

    /**
     * 6. Webhook giả mạo -> Trả về 401 Unauthorized.
     */
    public function test_case_6_forged_webhook_is_rejected_with_401(): void
    {
        $order = $this->createTestOrder(['total_amount' => 550000]);

        $response = $this->withHeaders([
            'Authorization' => 'Apikey invalid_hacker_token',
        ])->postJson('/api/webhooks/sepay', [
            'id' => 900006,
            'gateway' => 'Vietcombank',
            'accountNumber' => $this->bankAccount,
            'code' => $order->order_code,
            'content' => $order->order_code,
            'transferType' => 'in',
            'transferAmount' => 550000,
        ]);

        $response->assertStatus(401);
        $order->refresh();
        $this->assertNotSame('PAID', $order->payment_status);
    }

    /**
     * 7 & 9. Đơn hàng đã thanh toán / Hai giao dịch khác nhau cùng cập nhật một đơn hàng.
     */
    public function test_case_7_and_9_already_paid_order_or_second_transaction_does_not_overwrite(): void
    {
        $order = $this->createTestOrder(['total_amount' => 550000]);

        // Giao dịch 1 thanh toán thành công
        $this->withHeaders(['Authorization' => 'Apikey ' . $this->webhookToken])
            ->postJson('/api/webhooks/sepay', [
                'id' => 9000071,
                'gateway' => 'Vietcombank',
                'accountNumber' => $this->bankAccount,
                'code' => $order->order_code,
                'content' => $order->order_code,
                'transferType' => 'in',
                'transferAmount' => 550000,
                'referenceCode' => 'FT-FIRST-TX',
            ])->assertStatus(200)->assertJsonPath('status', PaymentTransaction::STATUS_MATCHED_PAID);

        // Giao dịch 2 tới cho cùng đơn hàng đã PAID
        $second = $this->withHeaders(['Authorization' => 'Apikey ' . $this->webhookToken])
            ->postJson('/api/webhooks/sepay', [
                'id' => 9000072,
                'gateway' => 'Vietcombank',
                'accountNumber' => $this->bankAccount,
                'code' => $order->order_code,
                'content' => $order->order_code,
                'transferType' => 'in',
                'transferAmount' => 550000,
                'referenceCode' => 'FT-SECOND-TX',
            ]);

        $second->assertStatus(200)
            ->assertJsonPath('status', PaymentTransaction::STATUS_ALREADY_PAID);

        $order->refresh();
        // Mã giao dịch gốc FT-FIRST-TX được giữ nguyên, không bị ghi đè
        $this->assertSame('FT-FIRST-TX', $order->transaction_code);
    }

    /**
     * 8 & 11. Đơn hàng hết hạn và giao dịch đến muộn sau khi đơn hàng hết hạn.
     */
    public function test_case_8_and_11_expired_order_and_late_payment_after_expiration(): void
    {
        $order = $this->createTestOrder([
            'total_amount' => 550000,
            'payment_expires_at' => now()->subMinutes(5),
        ]);
        $order->payment->update(['expires_at' => now()->subMinutes(5)]);

        // Kiểm tra API polling trả về trạng thái EXPIRED
        $statusRes = $this->withHeaders(['X-Customer-Phone' => $order->phone])
            ->getJson("/api/orders/{$order->order_code}/payment-status");

        $statusRes->assertStatus(200)
            ->assertJsonPath('expired', true)
            ->assertJsonPath('payment_status', Payment::STATUS_EXPIRED);

        // Giao dịch đến muộn sau khi đơn đã hết hạn -> LATE_PAYMENT_EXPIRED, không tự động PAID
        $webhookRes = $this->withHeaders(['Authorization' => 'Apikey ' . $this->webhookToken])
            ->postJson('/api/webhooks/sepay', [
                'id' => 900011,
                'gateway' => 'Vietcombank',
                'accountNumber' => $this->bankAccount,
                'code' => $order->order_code,
                'content' => $order->order_code,
                'transferType' => 'in',
                'transferAmount' => 550000,
                'referenceCode' => 'FT-LATE-TX',
            ]);

        $webhookRes->assertStatus(200)
            ->assertJsonPath('status', PaymentTransaction::STATUS_LATE_PAYMENT_EXPIRED);

        $order->refresh();
        $this->assertSame('EXPIRED', $order->payment_status);
    }

    /**
     * 10. Khách hàng truy cập đơn hàng của người khác (Chống IDOR).
     */
    public function test_case_10_customer_cannot_access_another_users_order(): void
    {
        $owner = User::query()->create([
            'name' => 'Chủ Đơn Hàng',
            'email' => 'owner_' . uniqid() . '@example.com',
            'phone' => '0901111222',
            'password' => bcrypt('password123'),
        ]);

        $intruder = User::query()->create([
            'name' => 'Người Dùng Khác',
            'email' => 'intruder_' . uniqid() . '@example.com',
            'phone' => '0903333444',
            'password' => bcrypt('password123'),
        ]);

        $order = $this->createTestOrder([
            'user_id' => $owner->id,
            'phone' => $owner->phone,
        ]);

        $this->actingAs($intruder)
            ->getJson("/api/orders/{$order->order_code}")
            ->assertStatus(403);

        $this->actingAs($intruder)
            ->getJson("/api/orders/{$order->order_code}/payment")
            ->assertStatus(403);

        $this->actingAs($intruder)
            ->getJson("/api/orders/{$order->order_code}/payment-status")
            ->assertStatus(403);
    }

    /**
     * 12. Giao dịch chưa đối soát được -> Admin đối soát thủ công có lưu người thực hiện và lý do.
     */
    public function test_case_12_admin_manual_reconciliation_records_actor_and_reason(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Đối Soát',
            'email' => 'admin_rec_' . uniqid() . '@shinebeauty.vn',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $order = $this->createTestOrder(['total_amount' => 550000]);

        // Giao dịch chuyển khoản ghi sai nội dung -> UNMATCHED_ORDER
        $this->withHeaders(['Authorization' => 'Apikey ' . $this->webhookToken])
            ->postJson('/api/webhooks/sepay', [
                'id' => 900012,
                'gateway' => 'Vietcombank',
                'accountNumber' => $this->bankAccount,
                'code' => '',
                'content' => 'CHUYEN KHOAN NHAM NOI DUNG',
                'transferType' => 'in',
                'transferAmount' => 550000,
                'referenceCode' => 'FT2631900012',
            ])->assertStatus(200);

        $tx = PaymentTransaction::query()->where('provider_transaction_id', '900012')->firstOrFail();
        $this->assertSame(PaymentTransaction::STATUS_UNMATCHED_ORDER, $tx->status);

        // Admin thực hiện đối soát thủ công kèm lý do
        $this->actingAs($admin)
            ->post(route('admin.payments.reconcile', $tx), [
                'order_id' => $order->id,
                'reason' => 'Khách hàng cung cấp biên lai FT2631900012 khớp số tiền 550.000₫ trên sao kê VCB.',
            ])
            ->assertRedirect();

        $tx->refresh();
        $order->refresh();

        $this->assertSame(PaymentTransaction::STATUS_MANUAL_RECONCILED, $tx->status);
        $this->assertSame($admin->id, $tx->reconciled_by);
        $this->assertStringContainsString('FT2631900012', (string) $tx->reconciliation_note);
        $this->assertSame('PAID', $order->payment_status);
        $this->assertSame('CONFIRMED', $order->order_status);
    }

    /**
     * 13. Ngưỡng miễn phí vận chuyển là từ 900.000₫ trở lên.
     */
    public function test_free_shipping_threshold_is_900000_vnd(): void
    {
        $checkoutService = app(\App\Services\CheckoutService::class);

        $underInner = $checkoutService->calculateShippingDetails(899000, 899000, 'ZONE_BASED', 'TP. Hồ Chí Minh', 'Quận 1');
        $this->assertSame(15000, $underInner['fee']);
        $this->assertSame('ZONE_INNER', $underInner['zone_code']);

        $underProvince = $checkoutService->calculateShippingDetails(899000, 899000, 'ZONE_BASED', 'Đà Nẵng', 'Quận Hải Châu');
        $this->assertSame(35000, $underProvince['fee']);
        $this->assertSame('ZONE_PROVINCE', $underProvince['zone_code']);

        $atThreshold = $checkoutService->calculateShippingDetails(900000, 900000, 'ZONE_BASED', 'Đà Nẵng', 'Quận Hải Châu');
        $this->assertSame(0, $atThreshold['fee']);
        $this->assertSame('FREE_SHIP', $atThreshold['zone_code']);

        $overThreshold = $checkoutService->calculateShippingDetails(950000, 950000, 'ZONE_BASED', 'TP. Hồ Chí Minh', 'Quận 1');
        $this->assertSame(0, $overThreshold['fee']);
        $this->assertSame('FREE_SHIP', $overThreshold['zone_code']);

        $summaryUnder = $checkoutService->calculateCartSummary(
            [['id' => 1, 'price' => 899000, 'quantity' => 1]],
            null,
            'ZONE_BASED',
            'TP. Hồ Chí Minh',
            'Quận 1'
        );
        $this->assertSame(900000, $summaryUnder['freeShippingThreshold']);
        $this->assertSame(1000, $summaryUnder['amountNeededForFreeShip']);
        $this->assertSame(15000, $summaryUnder['shippingFee']);
        $this->assertSame(914000, $summaryUnder['totalAmount']);
    }
}
