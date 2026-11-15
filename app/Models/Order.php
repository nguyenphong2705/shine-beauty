<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'user_id',
        'customer_name',
        'phone',
        'address',
        'note',
        'payment_method',
        'payment_status',
        'transaction_code',
        'paid_at',
        'payment_expires_at',
        'coupon_code',
        'discount_amount',
        'total_amount',
        'status',
        'order_status',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'payment_expires_at' => 'datetime',
        'total_amount' => 'integer',
        'discount_amount' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Order $order): void {
            if ($order->isDirty('status') && !$order->isDirty('order_status')) {
                $order->order_status = (string) $order->status;
            } elseif ($order->isDirty('order_status') && !$order->isDirty('status')) {
                $order->status = (string) $order->order_status;
            } elseif (empty($order->order_status) && !empty($order->status)) {
                $order->order_status = (string) $order->status;
            }
        });

        static::created(function (Order $order): void {
            $updates = [];
            if (empty($order->order_code)) {
                $updates['order_code'] = self::generateOrderCode((int) $order->id);
            }
            if (empty($order->payment_expires_at) && $order->isQrPayment()) {
                $timeout = max(1, (int) config('services.vietqr.payment_timeout_minutes', 15));
                $updates['payment_expires_at'] = ($order->created_at ?? now())->copy()->addMinutes($timeout);
            }
            if (!empty($updates)) {
                $order->forceFill($updates)->saveQuietly();
            }
        });
    }

    /**
     * Sinh mã đơn hàng duy nhất theo chuẩn SePay/VietQR (VD: DH2611150001).
     */
    public static function generateOrderCode(int $orderId): string
    {
        $prefix = strtoupper((string) config('services.sepay.match_prefix', 'DH'));

        return $prefix . now()->format('ymd') . str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tạo nội dung chuyển khoản chuẩn cho đơn hàng.
     */
    public function getTransferContentAttribute(): string
    {
        if ($this->relationLoaded('payment') && $this->payment?->transfer_content) {
            return (string) $this->payment->transfer_content;
        }

        return $this->order_code ?: self::generateOrderCode((int) $this->id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Kiểm tra đơn hàng có sử dụng phương thức thanh toán qua mã QR / Chuyển khoản ngân hàng hay không.
     */
    public function isQrPayment(): bool
    {
        return in_array($this->payment_method, ['BANK_TRANSFER', 'MOMO_QR'], true);
    }

    /**
     * Kiểm tra đơn hàng đã được xác thực thanh toán thành công hay chưa.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'PAID';
    }

    /**
     * Kiểm tra đơn hàng chuyển khoản đã hết thời gian thanh toán hay chưa.
     */
    public function isPaymentExpired(): bool
    {
        if ($this->isPaid()) {
            return false;
        }
        if (in_array($this->payment_status, ['EXPIRED', 'FAILED'], true) || $this->status === 'CANCELLED') {
            return true;
        }
        if ($this->payment_expires_at !== null && $this->payment_expires_at->isPast()) {
            return true;
        }

        return false;
    }

    /**
     * Nhãn hiển thị trạng thái thanh toán của đơn hàng.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        if ($this->isPaid()) {
            return 'Đã thanh toán cho đơn hàng';
        }

        if ($this->payment_status === 'UNDERPAID') {
            return 'Chuyển khoản thiếu tiền (Đơn hàng chưa thanh toán thành công)';
        }

        if ($this->payment_status === 'OVERPAID') {
            return 'Chuyển khoản thừa tiền — Chờ đối soát (Đơn hàng chưa thanh toán thành công)';
        }

        if ($this->payment_status === 'EXPIRED') {
            return 'Hết hạn thanh toán (Đơn hàng chưa thanh toán thành công)';
        }

        if ($this->isQrPayment()) {
            return 'Đơn hàng chưa thanh toán thành công';
        }

        return $this->status === 'COMPLETED'
            ? 'Đã thanh toán (COD)'
            : 'Thanh toán khi nhận hàng (Chưa thanh toán)';
    }

    /**
     * Nhãn trạng thái tổng hợp để hiển thị cho khách hàng và quản trị viên.
     */
    public function getDisplayStatusLabelAttribute(): string
    {
        if ($this->status === 'CANCELLED') {
            return 'Đã hủy';
        }

        if ($this->isQrPayment() && !$this->isPaid()) {
            return 'Đơn hàng chưa thanh toán thành công';
        }

        return match ($this->status) {
            'PENDING' => 'Chờ xác nhận',
            'CONFIRMED' => 'Đã xác nhận',
            'SHIPPING' => 'Đang giao hàng',
            'COMPLETED' => 'Hoàn thành',
            default => (string) $this->status,
        };
    }
}
