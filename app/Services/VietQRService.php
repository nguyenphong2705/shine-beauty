<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;

class VietQRService
{
    /**
     * Lấy cấu hình tài khoản ngân hàng VietQR từ backend config.
     *
     * @return array{bank_id: string, bank_name: string, bank_account: string, bank_account_name: string, qr_template: string, timeout_minutes: int}
     */
    public function getBankConfig(): array
    {
        return [
            'bank_id' => (string) config('services.vietqr.bank_id', 'VCB'),
            'bank_name' => (string) config('services.vietqr.bank_name', 'Vietcombank (VCB) - CN TP.HCM'),
            'bank_account' => (string) config('services.vietqr.bank_account', '1029384756'),
            'bank_account_name' => (string) config('services.vietqr.bank_account_name', 'SHINE BEAUTY COSMETICS'),
            'qr_template' => (string) config('services.vietqr.qr_template', 'compact2'),
            'timeout_minutes' => max(1, (int) config('services.vietqr.payment_timeout_minutes', 15)),
        ];
    }

    /**
     * Tạo thông tin mã QR VietQR chuẩn cho đơn hàng.
     *
     * @return array{
     *     bank_id: string,
     *     bank_name: string,
     *     account_number: string,
     *     account_name: string,
     *     qr_template: string,
     *     amount: int,
     *     transfer_content: string,
     *     qr_image_url: string,
     *     fallback_qr_url: string
     * }
     */
    public function generateQrData(Order $order, ?string $customTransferContent = null): array
    {
        $cfg = $this->getBankConfig();
        $amount = max(0, (int) $order->total_amount);
        $transferContent = trim($customTransferContent ?: $order->transfer_content);

        $qrImageUrl = sprintf(
            'https://img.vietqr.io/image/%s-%s-%s.png?amount=%d&addInfo=%s&accountName=%s',
            rawurlencode($cfg['bank_id']),
            rawurlencode($cfg['bank_account']),
            rawurlencode($cfg['qr_template']),
            $amount,
            urlencode($transferContent),
            urlencode($cfg['bank_account_name'])
        );

        $fallbackPayload = implode('|', [
            $cfg['bank_id'],
            $cfg['bank_account'],
            $cfg['bank_account_name'],
            (string) $amount,
            $transferContent,
        ]);

        $fallbackQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . urlencode($fallbackPayload);

        return [
            'bank_id' => $cfg['bank_id'],
            'bank_name' => $cfg['bank_name'],
            'account_number' => $cfg['bank_account'],
            'account_name' => $cfg['bank_account_name'],
            'qr_template' => $cfg['qr_template'],
            'amount' => $amount,
            'transfer_content' => $transferContent,
            'qr_image_url' => $qrImageUrl,
            'fallback_qr_url' => $fallbackQrUrl,
        ];
    }
}
