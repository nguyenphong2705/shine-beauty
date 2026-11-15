<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        private readonly VietQRService $vietQrService
    ) {
    }

    /**
     * Khởi tạo hoặc lấy bản ghi Payment cho đơn hàng chuyển khoản.
     */
    public function initializeOrderPayment(Order $order): Payment
    {
        return DB::transaction(function () use ($order): Payment {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (empty($lockedOrder->order_code)) {
                $lockedOrder->order_code = Order::generateOrderCode((int) $lockedOrder->id);
            }

            $bankCfg = $this->vietQrService->getBankConfig();
            if ($lockedOrder->isQrPayment() && empty($lockedOrder->payment_expires_at)) {
                $lockedOrder->payment_expires_at = ($lockedOrder->created_at ?? now())
                    ->copy()
                    ->addMinutes($bankCfg['timeout_minutes']);
            }

            if ($lockedOrder->isQrPayment() && in_array($lockedOrder->payment_status, ['UNPAID', ''], true)) {
                $lockedOrder->payment_status = Payment::STATUS_PENDING_PAYMENT;
            }

            $lockedOrder->save();

            $existingPayment = Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($existingPayment) {
                return $existingPayment;
            }

            $initialStatus = $lockedOrder->isPaid()
                ? Payment::STATUS_PAID
                : Payment::STATUS_PENDING_PAYMENT;

            $payment = new Payment([
                'order_id' => $lockedOrder->id,
                'amount' => (int) $lockedOrder->total_amount,
                'received_amount' => $lockedOrder->isPaid() ? (int) $lockedOrder->total_amount : 0,
                'payment_method' => $lockedOrder->payment_method,
                'transaction_code' => $lockedOrder->transaction_code,
                'transfer_content' => $lockedOrder->order_code,
                'bank_code' => $bankCfg['bank_id'],
                'bank_account' => $bankCfg['bank_account'],
                'status' => $initialStatus,
                'expires_at' => $lockedOrder->payment_expires_at,
                'paid_at' => $lockedOrder->paid_at,
                'status_history' => [],
            ]);

            $payment->appendStatusHistory(
                'INIT',
                $initialStatus,
                "Khởi tạo thông tin thanh toán cho đơn hàng {$lockedOrder->order_code}"
            );
            $payment->save();

            return $payment;
        });
    }

    /**
     * Đồng bộ trạng thái EXPIRED nếu đơn hàng chuyển khoản đã quá thời gian chờ thanh toán.
     */
    public function syncExpiredPaymentIfNeeded(Order $order): Order
    {
        if (!$order->isQrPayment() || $order->isPaid()) {
            return $order;
        }

        $payment = $order->payment ?? $this->initializeOrderPayment($order);
        $expiresAt = $payment->expires_at ?? $order->payment_expires_at;

        if ($expiresAt !== null && $expiresAt->isPast() && !in_array($order->payment_status, ['PAID', 'EXPIRED'], true)) {
            DB::transaction(function () use ($order, $payment): void {
                $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->first();
                $lockedPayment = Payment::query()->whereKey($payment->id)->lockForUpdate()->first();

                if (!$lockedOrder || !$lockedPayment || $lockedOrder->isPaid()) {
                    return;
                }

                $prevStatus = (string) $lockedPayment->status;
                $lockedPayment->status = Payment::STATUS_EXPIRED;
                $lockedPayment->appendStatusHistory(
                    $prevStatus,
                    Payment::STATUS_EXPIRED,
                    'Đơn hàng hết thời gian chờ thanh toán chuyển khoản.'
                );
                $lockedPayment->save();

                $lockedOrder->payment_status = Payment::STATUS_EXPIRED;
                $lockedOrder->save();
            });

            $order->refresh();
        }

        return $order;
    }

    /**
     * Lấy đầy đủ thông tin thanh toán và VietQR cho API / Frontend.
     *
     * @return array<string, mixed>
     */
    public function getOrderPaymentPayload(Order $order): array
    {
        $payment = $this->initializeOrderPayment($order);
        $order = $this->syncExpiredPaymentIfNeeded($order->fresh(['payment', 'items.product']));
        $payment = $order->payment ?? $payment->fresh();

        $qrData = $this->vietQrService->generateQrData($order, $payment->transfer_content);
        $expiresAt = $payment->expires_at ?? $order->payment_expires_at;
        $remainingSeconds = 0;

        if (!$order->isPaid() && $expiresAt !== null) {
            $remainingSeconds = max(0, (int) now()->diffInSeconds($expiresAt, false));
        }

        return [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'customer_name' => $order->customer_name,
            'phone' => $order->phone,
            'address' => $order->address,
            'total_amount' => (int) $order->total_amount,
            'received_amount' => (int) ($payment->received_amount ?? 0),
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'payment_status_label' => $order->payment_status_label,
            'order_status' => $order->order_status ?: $order->status,
            'order_status_label' => $order->display_status_label,
            'paid' => $order->isPaid(),
            'expired' => $order->isPaymentExpired(),
            'transaction_code' => $order->transaction_code ?? $payment->transaction_code,
            'transfer_content' => $payment->transfer_content,
            'paid_at' => $order->paid_at?->toIso8601String(),
            'paid_at_formatted' => $order->paid_at?->format('d/m/Y H:i:s'),
            'expires_at' => $expiresAt?->toIso8601String(),
            'expires_at_formatted' => $expiresAt?->format('d/m/Y H:i:s'),
            'remaining_seconds' => $remainingSeconds,
            'vietqr' => $qrData,
            'status_history' => $payment->status_history ?? [],
        ];
    }

    /**
     * Đối soát & xác nhận thanh toán thủ công dành cho Admin (có lưu vết người thực hiện và lý do).
     *
     * @return array{order: Order, payment: Payment, transaction: PaymentTransaction}
     */
    public function reconcileTransactionManually(
        PaymentTransaction $transaction,
        Order $order,
        User $admin,
        string $reason
    ): array {
        $cleanReason = trim($reason);
        if ($cleanReason === '') {
            throw new InvalidArgumentException('Vui lòng nhập lý do xác nhận đối soát thủ công.');
        }

        return DB::transaction(function () use ($transaction, $order, $admin, $cleanReason): array {
            $lockedTx = PaymentTransaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $payment = $this->initializeOrderPayment($lockedOrder);
            $lockedPayment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->isPaid()) {
                throw new InvalidArgumentException("Đơn hàng {$lockedOrder->order_code} đã ở trạng thái ĐÃ THANH TOÁN (PAID), không thể thanh toán lại.");
            }

            $txCode = $lockedTx->reference_code ?: ('SEPAY-' . $lockedTx->provider_transaction_id);
            $prevPaymentStatus = (string) $lockedPayment->status;

            $lockedTx->update([
                'order_id' => $lockedOrder->id,
                'payment_id' => $lockedPayment->id,
                'status' => PaymentTransaction::STATUS_MANUAL_RECONCILED,
                'reconciled_by' => $admin->id,
                'reconciled_at' => now(),
                'reconciliation_note' => $cleanReason,
            ]);

            $lockedPayment->status = Payment::STATUS_PAID;
            $lockedPayment->received_amount = (int) $lockedTx->amount;
            $lockedPayment->transaction_code = $txCode;
            $lockedPayment->paid_at = now();
            $lockedPayment->verified_by = $admin->id;
            $lockedPayment->verification_note = $cleanReason;
            $lockedPayment->appendStatusHistory(
                $prevPaymentStatus,
                Payment::STATUS_PAID,
                "Đối soát thủ công bởi Admin {$admin->name}: {$cleanReason}",
                (int) $admin->id,
                (string) $admin->name
            );
            $lockedPayment->save();

            $newOrderStatus = in_array($lockedOrder->status, ['PENDING', 'CANCELLED'], true)
                ? 'CONFIRMED'
                : $lockedOrder->status;

            $lockedOrder->update([
                'payment_status' => Payment::STATUS_PAID,
                'transaction_code' => $txCode,
                'paid_at' => now(),
                'status' => $newOrderStatus,
                'order_status' => $newOrderStatus,
            ]);

            Log::info('Admin manually reconciled bank transfer transaction', [
                'transaction_id' => $lockedTx->id,
                'provider_transaction_id' => $lockedTx->provider_transaction_id,
                'order_id' => $lockedOrder->id,
                'order_code' => $lockedOrder->order_code,
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'reason' => $cleanReason,
            ]);

            return [
                'order' => $lockedOrder->fresh(),
                'payment' => $lockedPayment->fresh(),
                'transaction' => $lockedTx->fresh(),
            ];
        });
    }
}
