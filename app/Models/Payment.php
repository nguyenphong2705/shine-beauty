<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';
    public const STATUS_PAID = 'PAID';
    public const STATUS_UNDERPAID = 'UNDERPAID';
    public const STATUS_OVERPAID = 'OVERPAID';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_FAILED = 'FAILED';

    protected $fillable = [
        'order_id',
        'amount',
        'received_amount',
        'payment_method',
        'transaction_code',
        'transfer_content',
        'bank_code',
        'bank_account',
        'status',
        'expires_at',
        'paid_at',
        'verified_by',
        'verification_note',
        'status_history',
    ];

    protected $casts = [
        'amount' => 'integer',
        'received_amount' => 'integer',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'status_history' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Ghi thêm 1 mục vào lịch sử thay đổi trạng thái thanh toán.
     */
    public function appendStatusHistory(
        string $fromStatus,
        string $toStatus,
        string $reason,
        ?int $actorId = null,
        ?string $actorName = null
    ): void {
        $history = is_array($this->status_history) ? $this->status_history : [];
        $history[] = [
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason,
            'actor_id' => $actorId,
            'actor_name' => $actorName ?? ($actorId ? "Admin #{$actorId}" : 'SePay Webhook / System'),
            'changed_at' => now()->toIso8601String(),
        ];
        $this->status_history = $history;
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_PAID) {
            return false;
        }
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
