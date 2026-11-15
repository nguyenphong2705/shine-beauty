<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'phone',
        'address',
        'note',
        'payment_method',
        'payment_status',
        'transaction_code',
        'paid_at',
        'coupon_code',
        'discount_amount',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Kiểm tra đơn hàng có sử dụng phương thức thanh toán qua mã QR hay không.
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
     * Nhãn hiển thị trạng thái thanh toán của đơn hàng.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        if ($this->isPaid()) {
            return 'Đã thanh toán cho đơn hàng';
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
