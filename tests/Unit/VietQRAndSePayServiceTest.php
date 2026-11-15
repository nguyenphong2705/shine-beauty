<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Order;
use App\Services\SePayService;
use App\Services\VietQRService;
use Illuminate\Http\Request;
use Tests\TestCase;

class VietQRAndSePayServiceTest extends TestCase
{
    public function test_vietqr_service_generates_accurate_qr_url_from_backend_config(): void
    {
        config([
            'services.vietqr.bank_id' => 'VCB',
            'services.vietqr.bank_name' => 'Vietcombank',
            'services.vietqr.bank_account' => '1029384756',
            'services.vietqr.bank_account_name' => 'SHINE BEAUTY COSMETICS',
            'services.vietqr.qr_template' => 'compact2',
        ]);

        $order = new Order([
            'order_code' => 'DH2611150099',
            'total_amount' => 650000,
        ]);

        $service = new VietQRService();
        $qr = $service->generateQrData($order, 'DH2611150099');

        $this->assertSame('VCB', $qr['bank_id']);
        $this->assertSame('1029384756', $qr['account_number']);
        $this->assertSame(650000, $qr['amount']);
        $this->assertSame('DH2611150099', $qr['transfer_content']);
        $this->assertStringContainsString('https://img.vietqr.io/image/VCB-1029384756-compact2.png', $qr['qr_image_url']);
        $this->assertStringContainsString('amount=650000', $qr['qr_image_url']);
        $this->assertStringContainsString('addInfo=DH2611150099', $qr['qr_image_url']);
    }

    public function test_sepay_webhook_signature_verification(): void
    {
        config(['services.sepay.webhook_token' => 'my_super_secret_sepay_key']);

        $sePayService = app(SePayService::class);

        $validRequest = Request::create('/api/webhooks/sepay', 'POST');
        $validRequest->headers->set('Authorization', 'Apikey my_super_secret_sepay_key');
        $this->assertTrue($sePayService->verifyWebhookSignature($validRequest));

        $forgedRequest = Request::create('/api/webhooks/sepay', 'POST');
        $forgedRequest->headers->set('Authorization', 'Apikey forged_wrong_token');
        $this->assertFalse($sePayService->verifyWebhookSignature($forgedRequest));

        $missingHeaderRequest = Request::create('/api/webhooks/sepay', 'POST');
        $this->assertFalse($sePayService->verifyWebhookSignature($missingHeaderRequest));
    }
}
