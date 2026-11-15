<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SePayService
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
    }

    /**
     * Xác thực chữ ký / API Key trong Header của SePay Webhook theo tài liệu chính thức:
     * Header: Authorization: Apikey <SEPAY_WEBHOOK_TOKEN>
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $configuredToken = trim((string) config('services.sepay.webhook_token', ''));
        if ($configuredToken === '') {
            Log::error('SePay webhook token is not configured in services.sepay.webhook_token');
            return false;
        }

        $authHeader = trim((string) $request->header('Authorization', ''));
        if ($authHeader === '') {
            return false;
        }

        $extractedToken = '';
        if (preg_match('/^(?:Apikey|Bearer)\s+(.+)$/i', $authHeader, $matches)) {
            $extractedToken = trim($matches[1]);
        } else {
            $extractedToken = $authHeader;
        }

        return $extractedToken !== '' && hash_equals($configuredToken, $extractedToken);
    }

    /**
     * Xử lý payload giao dịch từ SePay Webhook với Idempotency và Pessimistic Locking.
     *
     * Cấu trúc payload chuẩn từ SePay:
     * {
     *   "id": 92704,
     *   "gateway": "Vietcombank",
     *   "transactionDate": "2026-11-15 15:20:00",
     *   "accountNumber": "1029384756",
     *   "subAccount": null,
     *   "code": "DH2611150001",
     *   "content": "DH2611150001 chuyen tien",
     *   "transferType": "in",
     *   "description": "...",
     *   "transferAmount": 500000,
     *   "accumulated": 10500000,
     *   "referenceCode": "FT263190001"
     * }
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function processWebhook(array $payload): array
    {
        $providerTxId = trim((string) ($payload['id'] ?? $payload['provider_transaction_id'] ?? ''));
        if ($providerTxId === '') {
            return [
                'success' => false,
                'http_code' => 422,
                'status' => 'INVALID_PAYLOAD',
                'message' => 'Thiếu mã định danh giao dịch (id) từ SePay.',
            ];
        }

        $gateway = trim((string) ($payload['gateway'] ?? 'UNKNOWN'));
        $accountNumber = trim((string) ($payload['accountNumber'] ?? ''));
        $subAccount = trim((string) ($payload['subAccount'] ?? ''));
        $transferType = strtolower(trim((string) ($payload['transferType'] ?? 'in')));
        $rawAmount = $payload['transferAmount'] ?? $payload['amount'] ?? 0;
        $amount = is_numeric($rawAmount) ? max(0, (int) round((float) $rawAmount)) : 0;
        $code = trim((string) ($payload['code'] ?? ''));
        $content = trim((string) ($payload['content'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));
        $referenceCode = trim((string) ($payload['referenceCode'] ?? ''));
        $txDateRaw = $payload['transactionDate'] ?? null;

        $txDate = now();
        if (!empty($txDateRaw)) {
            try {
                $txDate = Carbon::parse((string) $txDateRaw);
            } catch (Throwable) {
                $txDate = now();
            }
        }

        return DB::transaction(function () use (
            $payload,
            $providerTxId,
            $gateway,
            $accountNumber,
            $subAccount,
            $transferType,
            $amount,
            $code,
            $content,
            $description,
            $referenceCode,
            $txDate
        ): array {
            // 1. Chống xử lý giao dịch trùng lặp (Idempotency)
            $existingTx = PaymentTransaction::query()
                ->where('provider_transaction_id', $providerTxId)
                ->lockForUpdate()
                ->first();

            if ($existingTx) {
                Log::info('SePay duplicate webhook ignored (idempotent)', [
                    'provider_transaction_id' => $providerTxId,
                    'existing_status' => $existingTx->status,
                ]);

                return [
                    'success' => true,
                    'http_code' => 200,
                    'duplicate' => true,
                    'status' => $existingTx->status,
                    'order_id' => $existingTx->order_id,
                    'transaction_id' => $existingTx->id,
                    'message' => 'Giao dịch đã được xử lý trước đó (Idempotent).',
                ];
            }

            $combinedContent = trim($code . ' ' . $content . ' ' . $description);

            // 2. Chỉ xử lý giao dịch tiền vào (transferType === 'in')
            if ($transferType !== 'in') {
                $tx = PaymentTransaction::query()->create([
                    'provider_transaction_id' => $providerTxId,
                    'reference_code' => $referenceCode ?: null,
                    'gateway' => $gateway,
                    'account_number' => $accountNumber ?: null,
                    'transfer_type' => $transferType,
                    'amount' => $amount,
                    'transfer_content' => $combinedContent,
                    'transaction_date' => $txDate,
                    'status' => PaymentTransaction::STATUS_IGNORED_OUTGOING,
                    'raw_payload' => $payload,
                ]);

                return [
                    'success' => true,
                    'http_code' => 200,
                    'duplicate' => false,
                    'status' => $tx->status,
                    'transaction_id' => $tx->id,
                    'message' => 'Bỏ qua giao dịch tiền ra (transferType != in).',
                ];
            }

            // 3. Kiểm tra đúng tài khoản ngân hàng nhận tiền (nếu cấu hình BANK_ACCOUNT có giá trị)
            $expectedAccount = preg_replace('/\s+/', '', (string) config('services.vietqr.bank_account', ''));
            $incomingAccount = preg_replace('/\s+/', '', $accountNumber);
            $incomingSubAccount = preg_replace('/\s+/', '', $subAccount);

            if (
                $expectedAccount !== ''
                && $incomingAccount !== ''
                && $incomingAccount !== $expectedAccount
                && $incomingSubAccount !== $expectedAccount
            ) {
                $tx = PaymentTransaction::query()->create([
                    'provider_transaction_id' => $providerTxId,
                    'reference_code' => $referenceCode ?: null,
                    'gateway' => $gateway,
                    'account_number' => $accountNumber,
                    'transfer_type' => $transferType,
                    'amount' => $amount,
                    'transfer_content' => $combinedContent,
                    'transaction_date' => $txDate,
                    'status' => PaymentTransaction::STATUS_INVALID_ACCOUNT,
                    'raw_payload' => $payload,
                ]);

                Log::warning('SePay webhook account number mismatch', [
                    'expected' => $expectedAccount,
                    'received' => $accountNumber,
                    'provider_transaction_id' => $providerTxId,
                ]);

                return [
                    'success' => true,
                    'http_code' => 200,
                    'duplicate' => false,
                    'status' => $tx->status,
                    'transaction_id' => $tx->id,
                    'message' => 'Số tài khoản nhận tiền không khớp cấu hình hệ thống.',
                ];
            }

            // 4. Tìm đơn hàng tương ứng từ mã code / nội dung chuyển khoản
            $matchedOrder = $this->findOrderByTransferInfo($code, $content, $description);

            if (!$matchedOrder) {
                $tx = PaymentTransaction::query()->create([
                    'provider_transaction_id' => $providerTxId,
                    'reference_code' => $referenceCode ?: null,
                    'gateway' => $gateway,
                    'account_number' => $accountNumber ?: null,
                    'transfer_type' => $transferType,
                    'amount' => $amount,
                    'transfer_content' => $combinedContent,
                    'transaction_date' => $txDate,
                    'status' => PaymentTransaction::STATUS_UNMATCHED_ORDER,
                    'raw_payload' => $payload,
                ]);

                Log::warning('SePay webhook unmatched order transfer content', [
                    'provider_transaction_id' => $providerTxId,
                    'content' => $combinedContent,
                    'amount' => $amount,
                ]);

                return [
                    'success' => true,
                    'http_code' => 200,
                    'duplicate' => false,
                    'status' => $tx->status,
                    'transaction_id' => $tx->id,
                    'message' => 'Không tìm thấy đơn hàng khớp với nội dung chuyển khoản. Đã lưu giao dịch chờ Admin đối soát.',
                ];
            }

            // 5. Khóa bản ghi Order và Payment để chống Race Condition khi có 2 giao dịch cùng lúc
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()->whereKey($matchedOrder->id)->lockForUpdate()->firstOrFail();
            $payment = $this->paymentService->initializeOrderPayment($lockedOrder);
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            $effectiveTxCode = $referenceCode !== '' ? $referenceCode : ('SEPAY-' . $providerTxId);

            // 6. Không cho phép thanh toán lại đơn đã PAID
            if ($lockedOrder->isPaid() || $lockedPayment->status === Payment::STATUS_PAID) {
                $tx = PaymentTransaction::query()->create([
                    'order_id' => $lockedOrder->id,
                    'payment_id' => $lockedPayment->id,
                    'provider_transaction_id' => $providerTxId,
                    'reference_code' => $effectiveTxCode,
                    'gateway' => $gateway,
                    'account_number' => $accountNumber ?: null,
                    'transfer_type' => $transferType,
                    'amount' => $amount,
                    'transfer_content' => $combinedContent,
                    'transaction_date' => $txDate,
                    'status' => PaymentTransaction::STATUS_ALREADY_PAID,
                    'raw_payload' => $payload,
                ]);

                $lockedPayment->appendStatusHistory(
                    Payment::STATUS_PAID,
                    Payment::STATUS_PAID,
                    "Nhận thêm giao dịch {$effectiveTxCode} ({$amount}₫) khi đơn đã PAID. Đã ghi nhận lịch sử đối soát."
                );
                $lockedPayment->save();

                return [
                    'success' => true,
                    'http_code' => 200,
                    'duplicate' => false,
                    'status' => $tx->status,
                    'order_id' => $lockedOrder->id,
                    'order_code' => $lockedOrder->order_code,
                    'transaction_id' => $tx->id,
                    'message' => 'Đơn hàng đã được thanh toán trước đó. Không xác nhận trùng lặp.',
                ];
            }

            // 7. Kiểm tra đơn hàng hết hạn hoặc đã hủy (Giao dịch đến muộn sau khi đơn hàng hết hạn)
            if ($lockedOrder->isPaymentExpired() || $lockedPayment->isExpired()) {
                $prevStatus = (string) $lockedPayment->status;
                $lockedPayment->status = Payment::STATUS_EXPIRED;
                $lockedPayment->received_amount = (int) $lockedPayment->received_amount + $amount;
                $lockedPayment->appendStatusHistory(
                    $prevStatus,
                    Payment::STATUS_EXPIRED,
                    "Giao dịch {$effectiveTxCode} ({$amount}₫) đến muộn sau khi đơn hàng đã hết hạn/hủy. Chờ Admin đối soát."
                );
                $lockedPayment->save();

                $lockedOrder->update([
                    'payment_status' => Payment::STATUS_EXPIRED,
                ]);

                $tx = PaymentTransaction::query()->create([
                    'order_id' => $lockedOrder->id,
                    'payment_id' => $lockedPayment->id,
                    'provider_transaction_id' => $providerTxId,
                    'reference_code' => $effectiveTxCode,
                    'gateway' => $gateway,
                    'account_number' => $accountNumber ?: null,
                    'transfer_type' => $transferType,
                    'amount' => $amount,
                    'transfer_content' => $combinedContent,
                    'transaction_date' => $txDate,
                    'status' => PaymentTransaction::STATUS_LATE_PAYMENT_EXPIRED,
                    'raw_payload' => $payload,
                ]);

                Log::warning('SePay late payment received for expired/cancelled order', [
                    'order_id' => $lockedOrder->id,
                    'order_code' => $lockedOrder->order_code,
                    'provider_transaction_id' => $providerTxId,
                    'amount' => $amount,
                ]);

                return [
                    'success' => true,
                    'http_code' => 200,
                    'duplicate' => false,
                    'status' => $tx->status,
                    'order_id' => $lockedOrder->id,
                    'order_code' => $lockedOrder->order_code,
                    'transaction_id' => $tx->id,
                    'message' => 'Giao dịch đến sau khi đơn hàng đã hết hạn. Đã ghi nhận chờ Admin đối soát.',
                ];
            }

            $expectedAmount = (int) $lockedOrder->total_amount;

            // 8. Kiểm tra giao dịch chuyển thiếu tiền (UNDERPAID)
            if ($amount < $expectedAmount) {
                $prevStatus = (string) $lockedPayment->status;
                $lockedPayment->status = Payment::STATUS_UNDERPAID;
                $lockedPayment->received_amount = $amount;
                $lockedPayment->transaction_code = $effectiveTxCode;
                $lockedPayment->appendStatusHistory(
                    $prevStatus,
                    Payment::STATUS_UNDERPAID,
                    "Khách chuyển thiếu tiền: nhận {$amount}₫ / cần {$expectedAmount}₫ (Mã GD: {$effectiveTxCode})."
                );
                $lockedPayment->save();

                $lockedOrder->update([
                    'payment_status' => Payment::STATUS_UNDERPAID,
                ]);

                $tx = PaymentTransaction::query()->create([
                    'order_id' => $lockedOrder->id,
                    'payment_id' => $lockedPayment->id,
                    'provider_transaction_id' => $providerTxId,
                    'reference_code' => $effectiveTxCode,
                    'gateway' => $gateway,
                    'account_number' => $accountNumber ?: null,
                    'transfer_type' => $transferType,
                    'amount' => $amount,
                    'transfer_content' => $combinedContent,
                    'transaction_date' => $txDate,
                    'status' => PaymentTransaction::STATUS_UNDERPAID,
                    'raw_payload' => $payload,
                ]);

                return [
                    'success' => true,
                    'http_code' => 200,
                    'duplicate' => false,
                    'status' => $tx->status,
                    'order_id' => $lockedOrder->id,
                    'order_code' => $lockedOrder->order_code,
                    'transaction_id' => $tx->id,
                    'message' => 'Số tiền chuyển khoản thấp hơn tổng tiền đơn hàng. Không tự động xác nhận PAID.',
                ];
            }

            // 9. Kiểm tra giao dịch chuyển thừa tiền (OVERPAID) — Không tự động xác nhận nếu dữ liệu không khớp chính xác
            if ($amount > $expectedAmount) {
                $prevStatus = (string) $lockedPayment->status;
                $lockedPayment->status = Payment::STATUS_OVERPAID;
                $lockedPayment->received_amount = $amount;
                $lockedPayment->transaction_code = $effectiveTxCode;
                $lockedPayment->appendStatusHistory(
                    $prevStatus,
                    Payment::STATUS_OVERPAID,
                    "Khách chuyển thừa tiền: nhận {$amount}₫ / cần {$expectedAmount}₫ (Mã GD: {$effectiveTxCode}). Chờ Admin đối soát."
                );
                $lockedPayment->save();

                $lockedOrder->update([
                    'payment_status' => Payment::STATUS_OVERPAID,
                ]);

                $tx = PaymentTransaction::query()->create([
                    'order_id' => $lockedOrder->id,
                    'payment_id' => $lockedPayment->id,
                    'provider_transaction_id' => $providerTxId,
                    'reference_code' => $effectiveTxCode,
                    'gateway' => $gateway,
                    'account_number' => $accountNumber ?: null,
                    'transfer_type' => $transferType,
                    'amount' => $amount,
                    'transfer_content' => $combinedContent,
                    'transaction_date' => $txDate,
                    'status' => PaymentTransaction::STATUS_OVERPAID,
                    'raw_payload' => $payload,
                ]);

                return [
                    'success' => true,
                    'http_code' => 200,
                    'duplicate' => false,
                    'status' => $tx->status,
                    'order_id' => $lockedOrder->id,
                    'order_code' => $lockedOrder->order_code,
                    'transaction_id' => $tx->id,
                    'message' => 'Số tiền chuyển khoản vượt quá tổng tiền đơn hàng. Đã lưu trạng thái OVERPAID chờ Admin đối soát.',
                ];
            }

            // 10. Khớp chính xác 100%: Cập nhật Payment -> PAID và Order -> CONFIRMED
            $prevStatus = (string) $lockedPayment->status;
            $lockedPayment->status = Payment::STATUS_PAID;
            $lockedPayment->received_amount = $amount;
            $lockedPayment->transaction_code = $effectiveTxCode;
            $lockedPayment->paid_at = $txDate;
            $lockedPayment->appendStatusHistory(
                $prevStatus,
                Payment::STATUS_PAID,
                "Xác nhận thanh toán tự động qua SePay Webhook (Mã GD: {$effectiveTxCode}, Số tiền: {$amount}₫)."
            );
            $lockedPayment->save();

            $nextOrderStatus = $lockedOrder->status === 'PENDING' ? 'CONFIRMED' : $lockedOrder->status;
            $lockedOrder->update([
                'payment_status' => Payment::STATUS_PAID,
                'transaction_code' => $effectiveTxCode,
                'paid_at' => $txDate,
                'status' => $nextOrderStatus,
                'order_status' => $nextOrderStatus,
            ]);

            $tx = PaymentTransaction::query()->create([
                'order_id' => $lockedOrder->id,
                'payment_id' => $lockedPayment->id,
                'provider_transaction_id' => $providerTxId,
                'reference_code' => $effectiveTxCode,
                'gateway' => $gateway,
                'account_number' => $accountNumber ?: null,
                'transfer_type' => $transferType,
                'amount' => $amount,
                'transfer_content' => $combinedContent,
                'transaction_date' => $txDate,
                'status' => PaymentTransaction::STATUS_MATCHED_PAID,
                'raw_payload' => $payload,
            ]);

            Log::info('SePay webhook matched and confirmed order payment', [
                'order_id' => $lockedOrder->id,
                'order_code' => $lockedOrder->order_code,
                'provider_transaction_id' => $providerTxId,
                'reference_code' => $effectiveTxCode,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'http_code' => 200,
                'duplicate' => false,
                'status' => PaymentTransaction::STATUS_MATCHED_PAID,
                'order_id' => $lockedOrder->id,
                'order_code' => $lockedOrder->order_code,
                'payment_status' => Payment::STATUS_PAID,
                'order_status' => $lockedOrder->order_status,
                'transaction_id' => $tx->id,
                'message' => "Thanh toán thành công cho đơn hàng {$lockedOrder->order_code}.",
            ];
        });
    }

    /**
     * Tìm đơn hàng theo mã code hoặc nội dung chuyển khoản.
     */
    public function findOrderByTransferInfo(string $code, string $content, string $description): ?Order
    {
        // 1. Khớp trực tiếp theo trường code nếu SePay đã bóc tách sẵn
        if ($code !== '') {
            $normalizedCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code) ?: '');
            if ($normalizedCode !== '') {
                $byCode = Order::query()->whereRaw('UPPER(order_code) = ?', [$normalizedCode])->first();
                if ($byCode) {
                    return $byCode;
                }

                $payment = Payment::query()->whereRaw('UPPER(transfer_content) = ?', [$normalizedCode])->first();
                if ($payment && $payment->order) {
                    return $payment->order;
                }
            }
        }

        $haystack = strtoupper($code . ' ' . $content . ' ' . $description);
        $prefix = preg_quote(strtoupper((string) config('services.sepay.match_prefix', 'DH')), '/');

        // 2. Tìm theo định dạng chuẩn DH + ymd + id (VD: DH2611150001)
        if (preg_match('/\b(' . $prefix . '\d{6,16})\b/i', $haystack, $m)) {
            $candidateCode = strtoupper($m[1]);
            $order = Order::query()->whereRaw('UPPER(order_code) = ?', [$candidateCode])->first();
            if ($order) {
                return $order;
            }
        }

        // 3. Hỗ trợ tương thích ngược định dạng SHINE ORDER<id> hoặc SHINEORDER<id>
        if (preg_match('/SHINE\s*ORDER\s*(\d+)/i', $haystack, $m)) {
            $orderId = (int) $m[1];
            return Order::query()->find($orderId);
        }

        return null;
    }
}
