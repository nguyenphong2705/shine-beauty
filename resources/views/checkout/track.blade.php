@extends('layouts.app')

@section('title', 'Tra Cứu Đơn Hàng Đã Mua | SHINE Beauty')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
    <div class="text-center space-y-2">
        <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Dành Cho Khách Mua Hàng</span>
        <h1 class="font-serif text-3xl font-bold text-stone-800">Tra Cứu Trạng Thái Đơn Hàng</h1>
        <p class="text-sm text-stone-500">Nhập số điện thoại đặt hàng để kiểm tra tiến độ xác nhận và giao hàng</p>
    </div>

    <!-- Search Form -->
    <form action="{{ route('orders.track') }}" method="GET" class="bg-white p-6 rounded-3xl border border-sage-100 shadow-xs flex flex-col sm:flex-row gap-3">
        <input type="text" name="phone" value="{{ $phone }}" required placeholder="Nhập số điện thoại đặt hàng (VD: 0909123456)..."
            class="flex-1 px-4 py-3 text-sm rounded-2xl border border-sage-200 bg-[#F5FAF7] focus:outline-none focus:ring-2 focus:ring-sage-300">
        <button type="submit" class="bg-sage-500 hover:bg-sage-600 text-white font-semibold px-7 py-3 rounded-2xl text-sm transition shadow-sm">
            🔍 Tra Cứu Đơn Hàng
        </button>
    </form>

    @if($phone !== '')
        @if($orders->isEmpty())
            <div class="bg-white p-10 rounded-3xl border border-sage-100 text-center space-y-3">
                <p class="font-serif text-lg font-semibold text-stone-700">
                    Không tìm thấy đơn hàng nào với số điện thoại "{{ $phone }}"
                </p>
                <p class="text-xs text-stone-400">Gợi ý: Bạn có thể thử số điện thoại mẫu <code>0909123456</code> hoặc <code>0918888999</code>.</p>
            </div>
        @else
            <div class="space-y-4">
                <p class="text-xs font-bold uppercase tracking-wider text-stone-500">
                    Tìm thấy {{ $orders->count() }} đơn hàng của SĐT {{ $phone }}
                </p>

                @foreach($orders as $order)
                    @php
                        $isQrUnpaid = $order->isQrPayment() && !$order->isPaid();
                        $isQrPaid = $order->isQrPayment() && $order->isPaid();
                        $pmLabel = match($order->payment_method) {
                            'BANK_TRANSFER' => 'Chuyển khoản QR Ngân hàng',
                            'MOMO_QR' => 'Thanh toán QR MoMo',
                            default => 'Thanh toán khi nhận hàng (COD)',
                        };
                    @endphp
                    <div class="bg-white p-6 rounded-3xl border {{ $isQrUnpaid && $order->status !== 'CANCELLED' ? 'border-amber-200' : 'border-sage-100' }} shadow-xs space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-sage-50 pb-3">
                            <div>
                                <span class="font-mono font-bold text-stone-800">#ORDER-{{ $order->id }}</span>
                                <span class="text-xs text-stone-400 ml-2">{{ $order->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            <span class="text-xs font-bold px-3 py-1 rounded-full border {{ $isQrUnpaid && $order->status !== 'CANCELLED' ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-sage-50 text-sage-600 border-sage-200' }}">
                                Trạng thái: {{ $order->display_status_label }}
                            </span>
                        </div>

                        <div class="text-xs text-stone-600 space-y-1">
                            <p><strong>Người nhận:</strong> {{ $order->customer_name }} ({{ $order->phone }})</p>
                            <p><strong>Địa chỉ giao:</strong> {{ $order->address }}</p>
                        </div>

                        @if($isQrUnpaid)
                            <div class="flex flex-wrap items-center justify-between gap-2 bg-amber-50/80 border border-amber-200 rounded-2xl px-4 py-2.5 text-xs">
                                <span class="font-bold text-amber-800">
                                    ⏳ Đơn hàng chưa thanh toán thành công
                                </span>
                                <a href="{{ route('checkout.index', ['success_order_id' => $order->id]) }}"
                                    class="font-bold text-amber-900 bg-white hover:bg-amber-100 border border-amber-300 px-3 py-1 rounded-full transition">
                                    💳 Quét QR &amp; Xác thực thanh toán ngay →
                                </a>
                            </div>
                        @elseif($isQrPaid)
                            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-2 text-xs font-bold text-emerald-800">
                                ✓ Đã thanh toán cho đơn hàng (Mã GD: {{ $order->transaction_code }})
                            </div>
                        @endif

                        <div class="border-t border-sage-50 pt-3 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-xs text-stone-500">
                                {{ $order->items->count() }} sản phẩm • {{ $pmLabel }}
                                @if($order->coupon_code)
                                    • Voucher <strong class="text-terracotta-700 bg-terracotta-50 px-2 py-0.5 rounded-full border border-terracotta-200">{{ $order->coupon_code }}</strong>
                                @endif
                            </div>
                            <div class="text-base font-bold text-sage-600">
                                Tổng thanh toán: {{ number_format($order->total_amount, 0, ',', '.') }}₫
                            </div>
                        </div>

                        @php
                            $canCancelTrackOrder = in_array($order->status, ['PENDING', 'CONFIRMED'], true) && !$order->isPaid();
                            $loggedUserPhone = auth()->check()
                                ? \App\Services\SmsGatewayService::normalizeVietnamPhone((string) (auth()->user()->phone ?? ''))
                                : '';
                            $trackOrderPhone = \App\Services\SmsGatewayService::normalizeVietnamPhone((string) ($order->phone ?? ''));
                            $isTrackPhoneOwner = auth()->check() && $loggedUserPhone !== '' && $loggedUserPhone === $trackOrderPhone;
                        @endphp
                        @if($canCancelTrackOrder)
                            <div class="pt-3 border-t border-dashed border-red-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-red-50/60 px-4 py-3 rounded-2xl">
                                <div class="flex items-start sm:items-center gap-2 text-xs text-red-700">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-100 text-red-600 font-bold flex-shrink-0">!</span>
                                    <span>
                                        <strong>Lưu ý hủy đơn:</strong> Phải đăng nhập đúng tài khoản có SĐT chính chủ (<strong>{{ $order->phone }}</strong>) mới hủy được đơn hàng này.
                                    </span>
                                </div>

                                @if($isTrackPhoneOwner)
                                    <form action="{{ route('customer.orders.cancel', $order) }}" method="POST"
                                          onsubmit="return confirmTrackCancelOrder(event, {{ $order->id }}, '{{ addslashes($order->phone) }}', true, true)"
                                          class="flex-shrink-0">
                                        @csrf
                                        <input type="hidden" name="cancel_reason" value="Khách hàng hủy đơn từ trang Tra cứu đơn hàng (đã xác thực đúng SĐT chính chủ)">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-full shadow-xs transition cursor-pointer">
                                            ✕ Hủy Đơn Hàng
                                        </button>
                                    </form>
                                @else
                                    <button type="button"
                                            onclick="confirmTrackCancelOrder(event, {{ $order->id }}, '{{ addslashes($order->phone) }}', {{ auth()->check() ? 'true' : 'false' }}, false)"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold bg-white hover:bg-red-100 text-red-600 border border-red-300 px-4 py-2 rounded-full shadow-2xs transition cursor-pointer flex-shrink-0">
                                        ✕ Hủy Đơn Hàng
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>

<script>
    function confirmTrackCancelOrder(event, orderId, orderPhone, isLoggedIn, isOwnerPhoneMatched) {
        if (!isLoggedIn) {
            event.preventDefault();
            alert('⚠️ Thông báo Hủy Đơn Hàng:\nVui lòng đăng nhập tài khoản có số điện thoại chính chủ (' + orderPhone + ') để hủy đơn hàng #ORDER-' + orderId + '!');
            if (typeof openAuthModal === 'function') {
                openAuthModal('login');
            }
            return false;
        }
        if (!isOwnerPhoneMatched) {
            event.preventDefault();
            alert('⚠️ Không thể hủy đơn hàng:\nTài khoản bạn đang đăng nhập không trùng với số điện thoại chính chủ của đơn hàng (' + orderPhone + '). Chỉ chính chủ SĐT đặt hàng mới có thể hủy đơn!');
            return false;
        }
        const ok = confirm('⚠️ Xác nhận Hủy Đơn Hàng #ORDER-' + orderId + ' (SĐT chính chủ: ' + orderPhone + '):\n\nBạn có chắc chắn muốn hủy đơn hàng này không?');
        if (!ok) {
            event.preventDefault();
            return false;
        }
        return true;
    }
</script>
@endsection
