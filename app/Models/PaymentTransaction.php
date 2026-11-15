<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    public const STATUS_MATCHED_PAID = 'MATCHED_PAID';
    public const STATUS_UNDERPAID = 'UNDERPAID';
    public const STATUS_OVERPAID = 'OVERPAID';
    public const STATUS_UNMATCHED_ORDER = 'UNMATCHED_ORDER';
    public const STATUS_LATE_PAYMENT_EXPIRED = 'LATE_PAYMENT_EXPIRED';
    public const STATUS_ALREADY_PAID = 'ALREADY_PAID';
    public const STATUS_INVALID_ACCOUNT = 'INVALID_ACCOUNT';
    public const STATUS_IGNORED_OUTGOING = 'IGNORED_OUTGOING';
    public const STATUS_MANUAL_RECONCILED = 'MANUAL_RECONCILED';

    protected $fillable = [
        'order_id',
        'payment_id',
        'provider_transaction_id',
        'reference_code',
        'gateway',
        'account_number',
        'transfer_type',
        'amount',
        'transfer_content',
        'transaction_date',
        'status',
        'reconciled_by',
        'reconciled_at',
        'reconciliation_note',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'integer',
        'transaction_date' => 'datetime',
        'reconciled_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function needsReconciliation(): bool
    {
        return in_array($this->status, [
            self::STATUS_UNMATCHED_ORDER,
            self::STATUS_UNDERPAID,
            self::STATUS_OVERPAID,
            self::STATUS_LATE_PAYMENT_EXPIRED,
        ], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MATCHED_PAID => 'Khớp tự động (PAID)',
            self::STATUS_MANUAL_RECONCILED => 'Đã đối soát thủ công',
            self::STATUS_UNDERPAID => 'Chuyển thiếu tiền',
            self::STATUS_OVERPAID => 'Chuyển thừa tiền',
            self::STATUS_UNMATCHED_ORDER => 'Sai/Không khớp mã đơn',
            self::STATUS_LATE_PAYMENT_EXPIRED => 'Chuyển muộn (Đơn hết hạn)',
            self::STATUS_ALREADY_PAID => 'Đơn đã thanh toán trước đó',
            self::STATUS_INVALID_ACCOUNT => 'Sai tài khoản nhận',
            self::STATUS_IGNORED_OUTGOING => 'Giao dịch tiền ra (Bỏ qua)',
            default => (string) $this->status,
        };
    }
}
