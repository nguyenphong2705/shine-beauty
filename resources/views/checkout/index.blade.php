@extends('layouts.app')

@section('title', 'Thanh Toán & Đặt Hàng | SHINE Beauty')

@section('content')
@php
    $freeShippingThreshold = $freeShippingThreshold ?? \App\Services\CheckoutService::FREE_SHIPPING_THRESHOLD;
    $standardShippingFee = $standardShippingFee ?? \App\Services\CheckoutService::STANDARD_SHIPPING_FEE;
    $subtotal = $subtotal ?? 0;
    $discountAmount = $discountAmount ?? 0;
    $afterDiscount = $afterDiscount ?? max(0, $subtotal - $discountAmount);
    $shippingFee = $shippingFee ?? (($subtotal > 0 && $afterDiscount < $freeShippingThreshold) ? $standardShippingFee : 0);
    $amountNeededForFreeShip = $amountNeededForFreeShip ?? max(0, $freeShippingThreshold - $afterDiscount);
    $totalAmount = $totalAmount ?? ($subtotal > 0 ? ($afterDiscount + $shippingFee) : 0);
    $selectedPaymentMethod = old('payment_method', 'COD');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    @if($successOrder)
        @php
            $successItemsSubtotal = $successOrder->items->sum(fn($i) => $i->price * $i->quantity);
            $successDiscount = $successOrder->discount_amount ?? 0;
            $successAfterDiscount = max(0, $successItemsSubtotal - $successDiscount);
            $successShippingFee = max(0, $successOrder->total_amount - $successAfterDiscount);
            $successPaymentLabel = match($successOrder->payment_method) {
                'BANK_TRANSFER' => 'Chuyển khoản Ngân hàng tự động (VietQR + SePay)',
                'MOMO_QR' => 'Ví điện tử MoMo (Mã QR MoMo)',
                default => 'Thanh toán khi nhận hàng (COD)',
            };
            $orderCodeDisplay = $successOrder->order_code ?: ('DH' . now()->format('ymd') . str_pad((string) $successOrder->id, 4, '0', STR_PAD_LEFT));
            $orderTransferCode = $paymentData['transfer_content'] ?? $orderCodeDisplay;
            $vietQrInfo = $paymentData['vietqr'] ?? app(\App\Services\VietQRService::class)->generateQrData($successOrder, $orderTransferCode);
            $remainingSeconds = $paymentData['remaining_seconds'] ?? 900;
            $isQrOrder = $successOrder->isQrPayment();
            $isOrderPaid = $successOrder->isPaid();
            $isOrderExpired = $successOrder->isPaymentExpired();
        @endphp
        <div id="bank-transfer-payment-root"
             data-order-code="{{ $orderCodeDisplay }}"
             data-remaining-seconds="{{ $remainingSeconds }}"
             data-payment-status="{{ $successOrder->payment_status }}"
             class="max-w-3xl mx-auto py-6 text-center space-y-6">
            <div class="bg-white p-6 sm:p-10 rounded-3xl border {{ $isQrOrder && !$isOrderPaid ? 'border-amber-200' : 'border-sage-100' }} shadow-lg space-y-6">
                @if($isQrOrder)
                    <!-- B. MÀN HÌNH ĐANG CHỜ THANH TOÁN CHUYỂN KHOẢN NGÂN HÀNG (PENDING_PAYMENT) -->
                    <div id="payment-view-pending" class="{{ (!$isOrderPaid && !$isOrderExpired && !in_array($successOrder->payment_status, ['UNDERPAID', 'OVERPAID', 'FAILED'], true)) ? '' : 'hidden' }} space-y-5">
                        <div class="w-18 h-18 mx-auto rounded-full bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-3xl font-bold">
                            ⏳
                        </div>
                        <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-amber-800 bg-amber-100 border border-amber-300 px-4 py-1.5 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-amber-600 animate-ping"></span>
                            <span id="sepay-polling-indicator">Đang chờ thanh toán • Tự động kiểm tra mỗi 3 giây</span>
                        </div>
                        <h1 class="font-serif text-2xl sm:text-3xl font-bold text-stone-800">
                            Đơn Hàng <span data-bind-order-code>{{ $orderCodeDisplay }}</span> (#ORDER-{{ $successOrder->id }}) — Chờ Chuyển Khoản
                        </h1>
                        <p class="text-sm text-stone-600 leading-relaxed max-w-xl mx-auto">
                            Vui lòng quét mã <strong>VietQR</strong> bên dưới bằng ứng dụng Ngân hàng. Hệ thống <strong>SePay Webhook</strong> sẽ tự động đối chiếu số tiền và nội dung chuyển khoản để xác nhận thanh toán tức thì.
                        </p>

                        <!-- Giao diện Bank Transfer Payment chuyên nghiệp -->
                        <div class="bg-blue-50/70 border-2 border-blue-200 rounded-3xl p-4 sm:p-6 text-left space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-blue-100 pb-3.5">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 block">Cổng Thanh Toán Tự Động VietQR × SePay</span>
                                    <h3 class="font-serif text-base sm:text-lg font-bold text-stone-800">Quét Mã QR Ngân Hàng Để Thanh Toán</h3>
                                </div>
                                <div class="flex items-center gap-2 bg-white px-3.5 py-1.5 rounded-2xl border border-amber-200 shadow-sm">
                                    <span class="text-[11px] font-semibold text-stone-500">Thời gian còn lại:</span>
                                    <strong id="vietqr-countdown-timer" class="font-mono text-base font-bold text-amber-700">
                                        {{ sprintf('%02d:%02d', intdiv((int) $remainingSeconds, 60), ((int) $remainingSeconds) % 60) }}
                                    </strong>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-5 items-center">
                                <div class="md:col-span-5 flex flex-col items-center bg-white p-4 rounded-2xl border border-blue-100 shadow-sm">
                                    <img id="vietqr-dynamic-image"
                                         src="{{ $vietQrInfo['qr_image_url'] }}"
                                         onerror="this.onerror=null;this.src='{{ $vietQrInfo['fallback_qr_url'] }}';"
                                         alt="Mã QR Ngân hàng VietQR"
                                         class="w-44 h-44 sm:w-52 sm:h-52 object-contain rounded-xl">
                                    <span class="text-[11px] text-blue-700 font-semibold mt-2 text-center">
                                        📷 Mở App Ngân hàng bất kỳ để quét mã QR
                                    </span>
                                </div>

                                <div class="md:col-span-7 space-y-2.5 text-xs">
                                    <div class="bg-white p-3 rounded-xl border border-blue-100 flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <span class="text-stone-400 block">Mã đơn hàng:</span>
                                            <strong data-bind-order-code class="font-mono text-sm text-stone-900 break-all">{{ $orderCodeDisplay }}</strong>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-[11px]">PENDING_PAYMENT</span>
                                    </div>

                                    <div class="bg-white p-3 rounded-xl border border-blue-100">
                                        <span class="text-stone-400 block">Tên ngân hàng thụ hưởng:</span>
                                        <strong id="vietqr-bank-name" class="text-stone-800 text-sm">{{ $vietQrInfo['bank_name'] }}</strong>
                                    </div>

                                    <div class="bg-white p-3 rounded-xl border border-blue-100 flex flex-wrap items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <span class="text-stone-400 block">Số tài khoản nhận tiền:</span>
                                            <strong id="vietqr-account-number" class="font-mono text-base text-blue-700 break-all">{{ $vietQrInfo['account_number'] }}</strong>
                                            <span class="block text-[11px] text-stone-500">Chủ TK: <strong id="vietqr-account-name">{{ $vietQrInfo['account_name'] }}</strong></span>
                                        </div>
                                        <button type="button"
                                                onclick="copyText(document.getElementById('vietqr-account-number').textContent, this)"
                                                class="px-3 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] transition flex-shrink-0 cursor-pointer">
                                            Sao chép STK
                                        </button>
                                    </div>

                                    <div class="bg-white p-3 rounded-xl border border-blue-100 flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <span class="text-stone-400 block">Số tiền cần thanh toán:</span>
                                            <strong id="vietqr-amount" class="text-base text-emerald-700">{{ number_format($successOrder->total_amount, 0, ',', '.') }}₫</strong>
                                        </div>
                                        <button type="button"
                                                onclick="copyText('{{ (int) $successOrder->total_amount }}', this)"
                                                class="px-3 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] transition flex-shrink-0 cursor-pointer">
                                            Sao chép số tiền
                                        </button>
                                    </div>

                                    <div class="bg-white p-3 rounded-xl border-2 border-blue-300 flex flex-wrap items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <span class="text-stone-400 block">Nội dung chuyển khoản (Bắt buộc giữ nguyên):</span>
                                            <strong id="vietqr-transfer-content" class="font-mono text-sm sm:text-base font-bold text-blue-800 break-all">{{ $orderTransferCode }}</strong>
                                        </div>
                                        <button type="button"
                                                onclick="copyText(document.getElementById('vietqr-transfer-content').textContent, this)"
                                                class="px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] transition flex-shrink-0 cursor-pointer">
                                            Sao chép nội dung
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white/90 border border-blue-100 rounded-2xl p-3.5 text-[11px] text-stone-600 flex flex-wrap items-center justify-between gap-2">
                                <span>🔒 Hệ thống chỉ xác nhận thanh toán khi nhận được Webhook xác thực giao dịch thật từ ngân hàng qua SePay.</span>
                                <button type="button"
                                        onclick="window.triggerManualStatusCheck && window.triggerManualStatusCheck()"
                                        class="px-3 py-1.5 rounded-xl bg-stone-900 hover:bg-sage-600 text-white font-bold text-[11px] transition cursor-pointer">
                                    🔄 Kiểm tra ngay
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- C. MÀN HÌNH THANH TOÁN THÀNH CÔNG (PAYMENT SUCCESS) -->
                    <div id="payment-view-success" class="{{ $isOrderPaid ? '' : 'hidden' }} space-y-5">
                        <div class="w-20 h-20 mx-auto rounded-full bg-emerald-600 text-white border-4 border-emerald-100 flex items-center justify-center text-4xl font-bold shadow-md">
                            ✓
                        </div>
                        <span class="inline-block text-xs font-bold uppercase tracking-widest text-emerald-800 bg-emerald-100 border border-emerald-300 px-4 py-1.5 rounded-full">
                            Thanh Toán Chuyển Khoản Thành Công (PAID)
                        </span>
                        <h1 class="font-serif text-2xl sm:text-3xl font-bold text-stone-800">
                            Đã Xác Nhận Thanh Toán Cho Đơn Hàng <span data-bind-order-code>{{ $orderCodeDisplay }}</span>!
                        </h1>
                        <p class="text-sm text-stone-600 leading-relaxed max-w-xl mx-auto">
                            Cảm ơn <strong>{{ $successOrder->customer_name }}</strong>! Hệ thống SePay đã xác thực giao dịch chuyển khoản ngân hàng và chuyển trạng thái đơn hàng sang <strong>ĐÃ XÁC NHẬN (CONFIRMED)</strong>.
                        </p>

                        <div class="bg-emerald-50/80 border-2 border-emerald-200 rounded-3xl p-6 text-left space-y-3 text-xs max-w-xl mx-auto">
                            <div class="flex items-center justify-between border-b border-emerald-200 pb-2.5">
                                <h3 class="font-serif text-base font-bold text-emerald-900">✓ Biên Lai Thanh Toán Điện Tử</h3>
                                <span class="text-[11px] font-bold bg-emerald-600 text-white px-3 py-0.5 rounded-full">PAID • CONFIRMED</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="bg-white p-3 rounded-xl border border-emerald-100">
                                    <span class="text-stone-400 block">Mã đơn hàng:</span>
                                    <strong data-bind-order-code class="font-mono text-sm text-stone-900">{{ $orderCodeDisplay }}</strong>
                                </div>
                                <div class="bg-white p-3 rounded-xl border border-emerald-100">
                                    <span class="text-stone-400 block">Số tiền đã thanh toán:</span>
                                    <strong data-bind-paid-amount class="text-sm text-emerald-700">{{ number_format($successOrder->total_amount, 0, ',', '.') }}₫</strong>
                                </div>
                                <div class="bg-white p-3 rounded-xl border border-emerald-100">
                                    <span class="text-stone-400 block">Mã giao dịch ngân hàng:</span>
                                    <strong data-bind-tx-code class="font-mono text-sm text-emerald-700">{{ $successOrder->transaction_code ?? 'SEPAY-VERIFIED' }}</strong>
                                </div>
                                <div class="bg-white p-3 rounded-xl border border-emerald-100">
                                    <span class="text-stone-400 block">Thời gian thanh toán:</span>
                                    <strong data-bind-paid-at class="text-stone-800">{{ $successOrder->paid_at?->format('d/m/Y H:i:s') ?? now()->format('d/m/Y H:i:s') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- D. MÀN HÌNH THANH TOÁN THẤT BẠI / HẾT HẠN (PAYMENT FAILED / EXPIRED) -->
                    <div id="payment-view-failed" class="{{ (!$isOrderPaid && ($isOrderExpired || in_array($successOrder->payment_status, ['UNDERPAID', 'OVERPAID', 'FAILED'], true))) ? '' : 'hidden' }} space-y-4">
                        <div class="w-20 h-20 mx-auto rounded-full bg-red-50 text-red-600 border-2 border-red-200 flex items-center justify-center text-3xl font-bold">
                            ✕
                        </div>
                        <span class="inline-block text-xs font-bold uppercase tracking-widest text-red-800 bg-red-100 border border-red-300 px-4 py-1.5 rounded-full">
                            Giao Dịch Chưa Hoàn Tất Hoặc Đã Hết Hạn
                        </span>
                        <h2 class="font-serif text-2xl font-bold text-stone-800">
                            Đơn Hàng <span data-bind-order-code>{{ $orderCodeDisplay }}</span> — {{ $successOrder->payment_status_label }}
                        </h2>
                        <p id="payment-failed-reason" class="text-sm text-stone-600 max-w-xl mx-auto leading-relaxed">
                            Đơn hàng đã hết thời gian thanh toán hoặc số tiền chuyển khoản chưa khớp với giá trị đơn hàng. Nếu bạn đã chuyển khoản, giao dịch đã được lưu lại để Quản trị viên đối soát thủ công.
                        </p>
                    </div>
                @else
                    <div class="w-20 h-20 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl font-bold">
                        ✓
                    </div>
                    <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Đặt Hàng Thành Công</span>
                    <h1 class="font-serif text-3xl font-bold text-stone-800">
                        Cảm Ơn {{ $successOrder->customer_name }} Đã Mua Sắm Tại SHINE Beauty!
                    </h1>
                    <p class="text-sm text-stone-600 leading-relaxed">
                        Đơn hàng thanh toán khi nhận hàng (COD) của bạn đã được ghi nhận vào hệ thống và đang chờ Shop xác nhận.
                    </p>
                @endif

                <div class="bg-[#F5FAF7] p-5 rounded-2xl border border-sage-100 text-left text-sm space-y-2.5">
                    <div class="flex justify-between gap-4">
                        <span class="text-stone-500">Mã đơn hàng:</span>
                        <span class="font-mono font-bold text-stone-800">{{ $orderCodeDisplay }} (#ORDER-{{ $successOrder->id }})</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-stone-500">Số điện thoại:</span>
                        <span class="font-semibold text-stone-800">{{ $successOrder->phone }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-stone-500 flex-shrink-0">Địa chỉ nhận hàng:</span>
                        <span class="font-semibold text-stone-800 text-right">{{ $successOrder->address }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-stone-500">Phương thức thanh toán:</span>
                        <span class="font-semibold text-stone-800 text-right">{{ $successPaymentLabel }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-4">
                        <span class="text-stone-500">Trạng thái đơn hàng:</span>
                        <span class="font-semibold text-stone-800 text-right">{{ $successOrder->display_status_label }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-4">
                        <span class="text-stone-500">Trạng thái thanh toán:</span>
                        @if($isQrOrder && !$isOrderPaid)
                            <span id="order-payment-status-badge" class="font-bold text-amber-800 bg-amber-100 px-3 py-1 rounded-full border border-amber-300 text-xs">
                                ⏳ {{ $successOrder->payment_status_label }}
                            </span>
                        @elseif($isQrOrder && $isOrderPaid)
                            <span id="order-payment-status-badge" class="font-bold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full border border-emerald-300 text-xs">
                                ✓ Đã thanh toán cho đơn hàng (Mã GD: {{ $successOrder->transaction_code }})
                            </span>
                        @else
                            <span class="font-semibold text-stone-700 text-xs">Thanh toán khi nhận hàng (COD)</span>
                        @endif
                    </div>
                    <div class="border-t border-sage-100 pt-2 space-y-1.5">
                        <div class="flex justify-between text-xs text-stone-500">
                            <span>Tạm tính sản phẩm:</span>
                            <span>{{ number_format($successItemsSubtotal, 0, ',', '.') }}₫</span>
                        </div>
                        @if($successOrder->coupon_code && $successDiscount > 0)
                            <div class="flex justify-between text-xs text-terracotta-700 font-semibold bg-terracotta-50 px-2.5 py-1 rounded-lg border border-terracotta-200">
                                <span>🎟️ Voucher ({{ $successOrder->coupon_code }}):</span>
                                <span>-{{ number_format($successDiscount, 0, ',', '.') }}₫</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-xs text-stone-500">
                            <span>Phí vận chuyển toàn quốc:</span>
                            @if($successShippingFee > 0)
                                <span class="font-semibold text-amber-600">+{{ number_format($successShippingFee, 0, ',', '.') }}₫ (Đơn dưới 900.000₫)</span>
                            @else
                                <span class="font-semibold text-emerald-600">Miễn phí (0₫)</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-between border-t border-sage-100 pt-2.5">
                        <span class="text-stone-700 font-bold">Tổng thanh toán:</span>
                        <span class="font-bold text-sage-600 text-lg">{{ number_format($successOrder->total_amount, 0, ',', '.') }}₫</span>
                    </div>
                </div>

                @php
                    $canCancelSuccessOrder = in_array($successOrder->status, ['PENDING', 'CONFIRMED'], true) && !$successOrder->isPaid();
                    $currentUserPhone = auth()->check()
                        ? \App\Services\SmsGatewayService::normalizeVietnamPhone((string) (auth()->user()->phone ?? ''))
                        : '';
                    $successOrderPhone = \App\Services\SmsGatewayService::normalizeVietnamPhone((string) ($successOrder->phone ?? ''));
                    $isSuccessOrderPhoneMatched = auth()->check() && $currentUserPhone !== '' && $currentUserPhone === $successOrderPhone;
                @endphp
                @if($canCancelSuccessOrder)
                    <div class="text-left bg-red-50/70 border border-red-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-start sm:items-center gap-2 text-xs text-red-700">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-100 text-red-600 font-bold flex-shrink-0">!</span>
                            <span>
                                <strong>Lưu ý Hủy Đơn Hàng:</strong> Phải đăng nhập đúng tài khoản có SĐT chính chủ (<strong>{{ $successOrder->phone }}</strong>) mới hủy được đơn hàng vừa đặt.
                            </span>
                        </div>
                        @if($isSuccessOrderPhoneMatched)
                            <form action="{{ route('customer.orders.cancel', $successOrder) }}" method="POST"
                                  data-confirm="true"
                                  data-confirm-type="danger"
                                  data-confirm-title="Xác nhận hủy đơn hàng"
                                  data-confirm-item="#ORDER-{{ $successOrder->id }} • SĐT: {{ $successOrder->phone }}"
                                  data-confirm-message="Bạn có chắc chắn muốn hủy đơn hàng này không?"
                                  data-confirm-detail="Đơn hàng sau khi hủy sẽ không thể khôi phục."
                                  data-confirm-btn="Xác nhận hủy đơn"
                                  class="flex-shrink-0">
                                @csrf
                                <input type="hidden" name="cancel_reason" value="Khách hàng hủy đơn ngay tại màn hình đặt hàng (đã xác thực đúng SĐT chính chủ)">
                                <button type="submit" class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs px-4 py-2.5 rounded-full shadow-xs transition cursor-pointer">
                                    ✕ Hủy Đơn Hàng
                                </button>
                            </form>
                        @else
                            <button type="button"
                                    onclick="shineNotice({ type: 'warning', title: 'Không thể hủy đơn hàng', itemLabel: '#ORDER-{{ $successOrder->id }}', message: 'Bạn phải đăng nhập đúng tài khoản có số điện thoại chính chủ đặt hàng ({{ addslashes($successOrder->phone) }}) mới được phép hủy đơn hàng này!' });"
                                    class="inline-flex items-center gap-1.5 bg-white hover:bg-red-100 text-red-600 border border-red-300 font-bold text-xs px-4 py-2.5 rounded-full transition cursor-pointer flex-shrink-0">
                                ✕ Hủy Đơn Hàng
                            </button>
                        @endif
                    </div>
                @endif

                <div class="flex flex-wrap justify-center gap-4 pt-2">
                    <a href="{{ route('orders.track', ['phone' => $successOrder->phone]) }}" class="bg-stone-900 hover:bg-stone-800 text-white font-semibold px-7 py-3 rounded-full text-sm transition">
                        🔍 Xem Chi Tiết Đơn Hàng
                    </a>
                    <a href="{{ route('products.index') }}" class="bg-sage-500 hover:bg-sage-600 text-white font-semibold px-7 py-3 rounded-full text-sm transition">
                        🛍️ Tiếp Tục Mua Sắm
                    </a>
                </div>
            </div>
        </div>
    @else
        <div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-sage-600 hover:text-sage-700 mb-2">
                ← Tiếp tục chọn mỹ phẩm
            </a>
            <h1 class="font-serif text-3xl font-bold text-stone-800">Thanh Toán & Giao Hàng</h1>
        </div>

        @if(empty($cart))
            <div class="bg-white rounded-3xl border border-sage-100 p-12 text-center space-y-4">
                <p class="font-serif text-xl text-stone-700">Giỏ hàng của bạn hiện chưa có sản phẩm nào</p>
                <a href="{{ route('products.index') }}" class="inline-block bg-sage-500 hover:bg-sage-600 text-white text-sm font-semibold px-6 py-3 rounded-full transition">
                    Khám phá sản phẩm ngay
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Shipping & Payment Form -->
                <form id="checkout-form" action="{{ route('checkout.store') }}" method="POST" class="lg:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-sage-100 shadow-xs space-y-6">
                    @csrf
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-sage-100">
                        <h2 class="font-serif text-xl font-bold text-stone-800">
                            1. Thông Tin Người Nhận & Địa Chỉ Giao Hàng
                        </h2>
                        @if(auth()->check() && !auth()->user()->is_admin)
                            <a href="{{ route('customer.addresses') }}" class="text-xs font-semibold text-sage-600 hover:underline">
                                ⚙️ Quản lý sổ địa chỉ ({{ isset($savedAddresses) ? $savedAddresses->count() : 0 }}) →
                            </a>
                        @endif
                    </div>

                    @if(isset($savedAddresses) && $savedAddresses->isNotEmpty())
                        @php
                            $activeSavedAddress = $defaultAddress ?? $savedAddresses->first();
                            $hasMultipleAddresses = $savedAddresses->count() > 1;
                        @endphp
                        <div class="p-4 rounded-2xl bg-sage-50/70 border border-sage-200 space-y-3 text-xs">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="font-bold text-sage-800 uppercase tracking-wider text-[11px]">
                                    📍 {{ $hasMultipleAddresses ? 'Địa Chỉ Giao Hàng Đã Lưu' : 'Chọn Nhanh Địa Chỉ Đã Lưu (Không Cần Nhập Lại)' }}
                                </span>
                                <div class="flex items-center gap-3">
                                    @if($hasMultipleAddresses)
                                        <button type="button"
                                            id="toggle-address-list-btn"
                                            onclick="toggleSavedAddressList()"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-sage-600 hover:bg-sage-700 text-white font-bold text-[11px] shadow-2xs transition cursor-pointer">
                                            🔄 Thay đổi địa chỉ ({{ $savedAddresses->count() }})
                                        </button>
                                    @endif
                                    <a href="{{ route('customer.addresses') }}" class="text-[11px] font-bold text-terracotta-600 hover:underline">
                                        + Thêm / Đặt địa chỉ mặc định
                                    </a>
                                </div>
                            </div>

                            @if($hasMultipleAddresses && $activeSavedAddress)
                                <div id="active-saved-address-summary" class="p-3 rounded-xl border-2 border-sage-500 bg-white shadow-2xs flex items-center justify-between gap-3">
                                    <div class="space-y-0.5 min-w-0">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span id="active-addr-label" class="font-bold text-stone-800">🏷️ {{ $activeSavedAddress->label }}</span>
                                            <span id="active-addr-default-badge" class="{{ $activeSavedAddress->is_default ? '' : 'hidden' }} text-[10px] font-bold bg-sage-600 text-white px-2 py-0.5 rounded-full">★ Mặc định</span>
                                            <span id="active-addr-recipient" class="text-stone-600 font-medium">
                                                • {{ $activeSavedAddress->recipient_name ?: auth()->user()->name }} ({{ $activeSavedAddress->recipient_phone ?: auth()->user()->phone }})
                                            </span>
                                        </div>
                                        <p id="active-addr-full" class="text-[11px] text-stone-600 truncate">
                                            {{ $activeSavedAddress->address }}
                                        </p>
                                    </div>
                                    <span class="text-[11px] font-bold text-sage-600 flex-shrink-0">✓ Đang chọn</span>
                                </div>
                            @endif

                            <div id="saved-address-list" class="grid grid-cols-1 gap-2 {{ $hasMultipleAddresses ? 'hidden pt-1 border-t border-sage-200/70' : '' }}">
                                @foreach($savedAddresses as $sAddr)
                                    <div data-saved-addr-card="{{ $sAddr->id }}"
                                        onclick='applySavedCustomerAddress(@json($sAddr), this)'
                                        class="p-3 rounded-xl border-2 transition cursor-pointer flex items-center justify-between gap-3 {{ $sAddr->is_default ? 'border-sage-500 bg-white shadow-2xs' : 'border-sage-100 bg-white/70 hover:bg-white hover:border-sage-300' }}">
                                        <div class="space-y-0.5 min-w-0">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="font-bold text-stone-800">🏷️ {{ $sAddr->label }}</span>
                                                @if($sAddr->is_default)
                                                    <span class="text-[10px] font-bold bg-sage-600 text-white px-2 py-0.5 rounded-full">★ Mặc định</span>
                                                @endif
                                                <span class="text-stone-600 font-medium">
                                                    • {{ $sAddr->recipient_name ?: auth()->user()->name }} ({{ $sAddr->recipient_phone ?: auth()->user()->phone }})
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-stone-600 truncate">
                                                {{ $sAddr->address }}
                                            </p>
                                        </div>
                                        <span class="text-[11px] font-bold text-sage-600 flex-shrink-0">
                                            Chọn giao →
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Họ và tên người nhận *</label>
                            <input type="text" id="checkout-customer-name" name="customer_name" required value="{{ old('customer_name', $defaultAddress->recipient_name ?? (auth()->user()->name ?? '')) }}" placeholder="VD: Nguyễn Thị Minh Châu" class="w-full px-4 py-3 text-sm rounded-2xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Số điện thoại liên hệ *</label>
                            <input type="tel" id="checkout-phone" name="phone" required value="{{ old('phone', $defaultAddress->recipient_phone ?? (auth()->user()->phone ?? '')) }}" placeholder="VD: 0909 123 456" class="w-full px-4 py-3 text-sm rounded-2xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-300">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Ghi chú đơn hàng (Tùy chọn)</label>
                        <textarea name="note" rows="2" placeholder="Ghi chú giao giờ hành chính hoặc gói quà tặng..." class="w-full px-4 py-3 text-sm rounded-2xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-300">{{ old('note') }}</textarea>
                    </div>

                    <!-- Địa chỉ giao hàng: Nhập địa chỉ bằng tay (Tỉnh/TP - Quận/Huyện - Phường/Xã + Số nhà, tên đường) -->
                    <div class="p-4 sm:p-5 rounded-3xl bg-[#F5FAF7] border border-sage-200/80 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-sage-700">
                                📍 Địa Chỉ Giao Hàng Chi Tiết *
                            </label>
                            <span class="text-[11px] text-stone-500">Chọn Tỉnh/Quận/Phường và nhập số nhà, tên đường</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="select-province" class="block text-[11px] font-semibold text-stone-600 mb-1.5">Tỉnh / Thành phố *</label>
                                <select id="select-province" name="province" required data-old="{{ old('province', $defaultAddress->province ?? 'TP. Hồ Chí Minh') }}"
                                    class="w-full px-3.5 py-2.5 text-sm rounded-2xl border border-sage-200 bg-white text-stone-800 focus:outline-none focus:ring-2 focus:ring-sage-300">
                                    <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                </select>
                            </div>

                            <div>
                                <label for="select-district" class="block text-[11px] font-semibold text-stone-600 mb-1.5">Quận / Huyện *</label>
                                <select id="select-district" name="district" required disabled data-old="{{ old('district', $defaultAddress->district ?? 'Quận 1') }}"
                                    class="w-full px-3.5 py-2.5 text-sm rounded-2xl border border-sage-200 bg-white text-stone-800 disabled:bg-stone-100 disabled:text-stone-400 focus:outline-none focus:ring-2 focus:ring-sage-300">
                                    <option value="">-- Chọn Quận/Huyện --</option>
                                </select>
                            </div>

                            <div>
                                <label for="select-ward" class="block text-[11px] font-semibold text-stone-600 mb-1.5">Phường / Xã *</label>
                                <select id="select-ward" name="ward" required disabled data-old="{{ old('ward', $defaultAddress->ward ?? 'Phường Bến Thành') }}"
                                    class="w-full px-3.5 py-2.5 text-sm rounded-2xl border border-sage-200 bg-white text-stone-800 disabled:bg-stone-100 disabled:text-stone-400 focus:outline-none focus:ring-2 focus:ring-sage-300">
                                    <option value="">-- Chọn Phường/Xã --</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="input-street" class="block text-[11px] font-semibold text-stone-600 mb-1.5">
                                Số nhà, tên đường, tòa nhà / hẻm *
                            </label>
                            <input type="text" id="input-street" name="street_address" required value="{{ old('street_address', $defaultAddress->street_address ?? ($defaultAddress->address ?? (auth()->user()->address ?? ''))) }}"
                                placeholder="VD: Số 28 Phạm Văn Chiêu"
                                class="w-full px-4 py-3 text-sm rounded-2xl border border-sage-200 bg-white focus:outline-none focus:ring-2 focus:ring-sage-300">
                        </div>

                        <!-- Hidden full address synced for backend & live preview -->
                        <input type="hidden" id="input-full-address" name="address" value="{{ old('address', $defaultAddress->address ?? (auth()->user()->address ?? '')) }}">
                        <div class="text-xs text-stone-600 bg-white/90 px-3.5 py-2.5 rounded-2xl border border-sage-100 flex items-start gap-2">
                            <span class="text-sage-600 font-bold flex-shrink-0">Giao đến:</span>
                            <span id="address-preview-text" class="font-medium text-stone-800">
                                {{ old('address', $defaultAddress->address ?? (auth()->user()->address ?? 'Chọn Tỉnh/TP, Quận/Huyện, Phường/Xã và nhập số nhà, tên đường.')) }}
                            </span>
                        </div>

                        <!-- CHỌN PHƯƠNG THỨC TÍNH PHÍ VẬN CHUYỂN (FLAT RATE vs ZONE-BASED CHO ĐƠN DƯỚI 900.000₫) -->
                        @php
                            $selectedShippingMode = old('shipping_mode', $shippingMode ?? 'ZONE_BASED');
                        @endphp
                        <div class="pt-3 border-t border-sage-200/80 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-sage-700">
                                    🚚 Biểu Phí Vận Chuyển (Áp dụng khi đơn hàng &lt; 900.000₫)
                                </label>
                                <span id="active-shipping-zone-badge" class="text-[11px] font-bold px-3 py-1 rounded-full bg-sage-600 text-white shadow-2xs">
                                    {{ $shippingZoneLabel ?? 'Khu vực Nội thành: 15.000₫' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <!-- Option 1: Tính theo khu vực (Zone-based) -->
                                <label data-ship-mode-card="ZONE_BASED" class="p-3.5 rounded-2xl border-2 transition cursor-pointer flex items-start gap-2.5 {{ $selectedShippingMode === 'ZONE_BASED' ? 'border-sage-500 bg-white shadow-xs' : 'border-sage-200 bg-white/60 hover:bg-white' }}">
                                    <input type="radio" name="shipping_mode" value="ZONE_BASED" {{ $selectedShippingMode === 'ZONE_BASED' ? 'checked' : '' }} onchange="recalculateShippingAndTotal()" class="mt-0.5 accent-sage-600">
                                    <div class="space-y-1 flex-1">
                                        <p class="font-bold text-stone-800">1. Tính theo khu vực (Zone-based)</p>
                                        <ul class="text-[11px] text-stone-600 space-y-0.5 leading-snug">
                                            <li>• Nội thành TP.HCM / Hà Nội: <strong class="text-sage-700">15.000₫</strong></li>
                                            <li>• Ngoại thành TP.HCM / Hà Nội: <strong class="text-sage-700">25.000₫</strong></li>
                                            <li>• Liên tỉnh (Tỉnh/TP khác): <strong class="text-sage-700">35.000₫</strong></li>
                                            <li>• Vùng sâu vùng xa / Hải đảo: <strong class="text-sage-700">50.000₫</strong></li>
                                        </ul>
                                    </div>
                                </label>

                                <!-- Option 2: Phí cố định (Flat Rate) -->
                                <label data-ship-mode-card="FLAT_RATE" class="p-3.5 rounded-2xl border-2 transition cursor-pointer flex items-start gap-2.5 {{ $selectedShippingMode === 'FLAT_RATE' ? 'border-sage-500 bg-white shadow-xs' : 'border-sage-200 bg-white/60 hover:bg-white' }}">
                                    <input type="radio" name="shipping_mode" value="FLAT_RATE" {{ $selectedShippingMode === 'FLAT_RATE' ? 'checked' : '' }} onchange="recalculateShippingAndTotal()" class="mt-0.5 accent-sage-600">
                                    <div class="space-y-1 flex-1">
                                        <p class="font-bold text-stone-800">2. Phí cố định (Flat Rate)</p>
                                        <ul class="text-[11px] text-stone-600 space-y-0.5 leading-snug">
                                            <li>• Nội thành (TP.HCM / Hà Nội): <strong class="text-sage-700">15.000₫</strong></li>
                                            <li>• Ngoại tỉnh / Ngoại thành: <strong class="text-sage-700">35.000₫</strong></li>
                                            <li class="text-stone-400 italic pt-1">• Đơn từ 900.000₫: Miễn phí toàn quốc (0₫)</li>
                                        </ul>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Cart Summary -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-sage-100 shadow-xs space-y-6">
                        <h2 class="font-serif text-xl font-bold text-stone-800 pb-3 border-b border-sage-100">
                            Chi Tiết Giỏ Hàng ({{ count($cart) }})
                        </h2>

                        <div class="space-y-4 max-h-96 overflow-y-auto pr-1">
                            @foreach($cart as $item)
                                <div class="flex gap-3.5 items-center pb-4 border-b border-sage-50">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-16 h-16 rounded-2xl object-cover bg-sage-50 flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-stone-800 line-clamp-1">{{ $item['name'] }}</p>
                                        <p class="text-xs text-stone-400">{{ $item['volume'] }} • {{ $item['skin_type'] }}</p>
                                        <p class="text-sm font-bold text-sage-600 mt-0.5">
                                            {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}₫
                                            @if($item['quantity'] > 1)
                                                <span class="text-[11px] font-normal text-stone-400">({{ number_format($item['price'], 0, ',', '.') }}₫ x {{ $item['quantity'] }})</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="inline-flex items-center border border-sage-200 rounded-full bg-[#F5FAF7] px-1 py-0.5 flex-shrink-0">
                                        <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                            <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                            <button type="submit" title="Giảm 1 sản phẩm" class="w-7 h-7 rounded-full hover:bg-sage-200/70 text-stone-700 hover:text-sage-700 font-bold text-xs flex items-center justify-center transition cursor-pointer">−</button>
                                        </form>
                                        <span class="w-7 text-center text-xs font-bold text-stone-800">{{ $item['quantity'] }}</span>
                                        <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                            <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                            <button type="submit" title="Tăng 1 sản phẩm" class="w-7 h-7 rounded-full hover:bg-sage-200/70 text-stone-700 hover:text-sage-700 font-bold text-xs flex items-center justify-center transition cursor-pointer">+</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Mã giảm giá (Voucher) - Tone nền Terracotta sang trọng, chỉ cho phép áp dụng duy nhất 1 voucher -->
                        <div class="p-4 rounded-3xl bg-gradient-to-br from-terracotta-50 via-[#FFF8F5] to-terracotta-100/80 border border-terracotta-200 space-y-3 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold uppercase tracking-wider text-terracotta-800">
                                    🎟️ Mã Giảm Giá / Voucher
                                </label>
                                <span class="text-[11px] font-semibold text-white bg-terracotta-600 px-2.5 py-0.5 rounded-full shadow-2xs">
                                    Tối đa 01 voucher / đơn
                                </span>
                            </div>

                            @if(!empty($appliedCoupon))
                                <div class="bg-terracotta-600 text-white border border-terracotta-700 p-3.5 rounded-2xl text-xs space-y-2 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-terracotta-50">
                                            ✓ Đang áp dụng (1/1): <strong class="font-mono text-sm text-white underline">{{ $appliedCoupon['code'] }}</strong> (-{{ $appliedCoupon['discount_percent'] }}%)
                                        </span>
                                        <form action="{{ route('checkout.coupon.remove') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-terracotta-800 bg-white hover:bg-terracotta-50 px-2.5 py-1 rounded-xl font-bold transition shadow-2xs cursor-pointer">
                                                ✕ Gỡ mã
                                            </button>
                                        </form>
                                    </div>
                                    <p class="text-[11px] text-terracotta-100">
                                        🔒 Mỗi đơn hàng chỉ được sử dụng <strong>01 mã giảm giá duy nhất</strong>. Nếu muốn đổi sang mã khác, vui lòng bấm <strong>"Gỡ mã"</strong> trước.
                                    </p>
                                </div>

                                @if(!empty($activeCoupons) && $activeCoupons->count() > 0)
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        @foreach($activeCoupons as $cp)
                                            @if($appliedCoupon['code'] === $cp->code)
                                                <span class="text-[11px] bg-terracotta-700 text-white border border-terracotta-500 px-2.5 py-1 rounded-full font-mono font-semibold shadow-2xs">
                                                    ✓ {{ $cp->code }} (-{{ $cp->discount_percent }}%)
                                                </span>
                                            @else
                                                <button type="button" disabled title="Không thể áp dụng 2 mã giảm giá cùng lúc. Hãy gỡ mã {{ $appliedCoupon['code'] }} nếu muốn đổi mã."
                                                    class="text-[11px] bg-terracotta-100/60 text-terracotta-400 border border-terracotta-200 px-2.5 py-1 rounded-full font-mono font-semibold cursor-not-allowed opacity-70">
                                                    🔒 {{ $cp->code }} (-{{ $cp->discount_percent }}%)
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <form action="{{ route('checkout.coupon.apply') }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="coupon_code" placeholder="Nhập 1 mã (VD: LUMINA20)" class="flex-1 px-3.5 py-2 text-xs font-mono uppercase rounded-2xl border border-terracotta-200 bg-white text-terracotta-900 placeholder-terracotta-400 focus:outline-none focus:ring-2 focus:ring-terracotta-400">
                                    <button type="submit" class="bg-terracotta-600 hover:bg-terracotta-700 text-white text-xs font-semibold px-4 py-2 rounded-2xl transition shadow-xs cursor-pointer">
                                        Áp dụng
                                    </button>
                                </form>
                                <p class="text-[11px] text-terracotta-800/80">
                                    * Lưu ý: Chỉ chọn 01 mã giảm giá cho đơn hàng (không cộng dồn 2 mã).
                                </p>
                                @if(!empty($activeCoupons) && $activeCoupons->count() > 0)
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        @foreach($activeCoupons as $cp)
                                            @php
                                                $eligible = $subtotal >= ($cp->min_order_amount ?? 0);
                                            @endphp
                                            <form action="{{ route('checkout.coupon.apply') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="coupon_code" value="{{ $cp->code }}">
                                                <button type="submit"
                                                    title="{{ $eligible ? 'Bấm để áp dụng mã ' . $cp->code : 'Đơn tối thiểu ' . number_format($cp->min_order_amount, 0, ',', '.') . '₫' }}"
                                                    class="text-[11px] {{ $eligible ? 'bg-terracotta-600 hover:bg-terracotta-700 text-white border-terracotta-600 cursor-pointer shadow-2xs' : 'bg-white/80 text-terracotta-400 border-terracotta-200 hover:bg-terracotta-50' }} border px-2.5 py-1 rounded-full font-mono font-semibold transition">
                                                    {{ $cp->code }} (-{{ $cp->discount_percent }}%)
                                                    @if(($cp->min_order_amount ?? 0) > 0)
                                                        <span class="font-sans font-normal text-[10px] opacity-85">• từ {{ number_format($cp->min_order_amount / 1000, 0, ',', '.') }}k</span>
                                                    @endif
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Thông báo & Thanh tiến trình Miễn phí vận chuyển toàn quốc (Đơn từ 900.000₫) -->
                        <div id="shipping-notice-box" class="p-3.5 rounded-2xl border {{ $shippingFee === 0 ? 'bg-emerald-50/70 border-emerald-200' : 'bg-terracotta-50/80 border-terracotta-200' }} space-y-2 text-xs">
                            @if($shippingFee === 0)
                                <div class="flex items-center justify-between text-emerald-800 font-semibold">
                                    <span>🚚 Miễn phí vận chuyển toàn quốc</span>
                                    <span class="bg-emerald-600 text-white text-[10px] px-2 py-0.5 rounded-full">Đơn ≥ 900.000₫</span>
                                </div>
                                <p class="text-[11px] text-emerald-700">
                                    Giá trị đơn hàng đạt <strong>{{ number_format($afterDiscount, 0, ',', '.') }}₫</strong> (từ 900.000₫ trở lên), bạn được miễn phí ship toàn quốc!
                                </p>
                            @else
                                <div class="flex items-center justify-between text-terracotta-900 font-semibold">
                                    <span id="shipping-box-title">🚚 {{ $shippingZoneLabel ?? ('Phí ship: +' . number_format($shippingFee, 0, ',', '.') . '₫') }}</span>
                                    <span class="bg-terracotta-200/80 text-terracotta-900 text-[10px] px-2 py-0.5 rounded-full flex-shrink-0">Đơn &lt; 900.000₫</span>
                                </div>
                                <p class="text-[11px] text-terracotta-800">
                                    Giá trị đơn hiện tại là <strong>{{ number_format($afterDiscount, 0, ',', '.') }}₫</strong> (nhỏ hơn 900.000₫). Mua thêm <strong class="text-terracotta-600">{{ number_format($amountNeededForFreeShip, 0, ',', '.') }}₫</strong> để được <strong>Miễn phí vận chuyển toàn quốc</strong>!
                                </p>
                                <div class="w-full h-2 bg-terracotta-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-terracotta-600 rounded-full transition-all duration-300" style="width: {{ min(100, (int) round(($afterDiscount / $freeShippingThreshold) * 100)) }}%"></div>
                                </div>
                            @endif
                        </div>

                        <!-- Tổng kết tiền đơn hàng -->
                        <div class="space-y-2 text-sm pt-2">
                            <div class="flex justify-between text-stone-500">
                                <span>Tạm tính ({{ collect($cart)->sum('quantity') }} sản phẩm)</span>
                                <span>{{ number_format($subtotal, 0, ',', '.') }}₫</span>
                            </div>
                            @if(!empty($discountAmount) && $discountAmount > 0)
                                <div class="flex justify-between text-terracotta-700 font-semibold bg-terracotta-50 px-2.5 py-1.5 rounded-xl border border-terracotta-200">
                                    <span>🎟️ Giảm giá Voucher ({{ $appliedCoupon['code'] ?? '' }})</span>
                                    <span>-{{ number_format($discountAmount, 0, ',', '.') }}₫</span>
                                </div>
                                <div class="flex justify-between text-stone-600 text-xs">
                                    <span>Giá trị đơn sau giảm giá</span>
                                    <span class="font-semibold">{{ number_format($afterDiscount, 0, ',', '.') }}₫</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center text-stone-500">
                                <div>
                                    <span>Phí vận chuyển</span>
                                    <span id="summary-shipping-zone-sub" class="block text-[11px] text-stone-400">
                                        {{ $shippingZoneLabel ?? '' }}
                                    </span>
                                </div>
                                @if($shippingFee > 0)
                                    <span id="summary-shipping-fee-text" class="text-amber-600 font-bold">+{{ number_format($shippingFee, 0, ',', '.') }}₫</span>
                                @else
                                    <span id="summary-shipping-fee-text" class="text-emerald-600 font-semibold">Miễn phí (0₫)</span>
                                @endif
                            </div>
                            <div class="flex justify-between text-lg font-bold text-stone-800 pt-3 border-t border-sage-100">
                                <span>Tổng thanh toán</span>
                                <span id="summary-total-amount-text" class="text-sage-600">{{ number_format($totalAmount, 0, ',', '.') }}₫</span>
                            </div>
                        </div>

                        <!-- Phương Thức Thanh Toán & Hoàn Tất Đặt Hàng (Cột phải sau Tổng thanh toán) -->
                        <div class="pt-4 border-t border-sage-100 space-y-4">
                            <h2 class="font-serif text-xl font-bold text-stone-800">
                                2. Phương Thức Thanh Toán
                            </h2>

                            <!-- 3 Payment Options: COD, Bank QR, MoMo QR -->
                            <div class="grid grid-cols-1 gap-3">
                                <label data-payment-card="COD" class="p-4 rounded-2xl border-2 transition flex items-start gap-3 cursor-pointer {{ $selectedPaymentMethod === 'COD' ? 'border-sage-500 bg-sage-50/70 shadow-xs' : 'border-sage-200 bg-[#F5FAF7] hover:bg-sage-50/40' }}">
                                    <input type="radio" name="payment_method" value="COD" form="checkout-form" {{ $selectedPaymentMethod === 'COD' ? 'checked' : '' }} onchange="switchPaymentMethod('COD')" class="mt-1 accent-sage-500">
                                    <div>
                                        <p class="text-sm font-bold text-stone-800">💵 Thanh toán COD</p>
                                        <p class="text-[11px] text-stone-500 mt-0.5 leading-snug">Kiểm tra tem niêm phong khi nhận hàng rồi thanh toán</p>
                                    </div>
                                </label>

                                <label data-payment-card="BANK_TRANSFER" class="p-4 rounded-2xl border-2 transition flex items-start gap-3 cursor-pointer {{ $selectedPaymentMethod === 'BANK_TRANSFER' ? 'border-blue-500 bg-blue-50/50 shadow-xs' : 'border-sage-200 bg-[#F5FAF7] hover:bg-sage-50/40' }}">
                                    <input type="radio" name="payment_method" value="BANK_TRANSFER" form="checkout-form" {{ $selectedPaymentMethod === 'BANK_TRANSFER' ? 'checked' : '' }} onchange="switchPaymentMethod('BANK_TRANSFER')" class="mt-1 accent-blue-600">
                                    <div>
                                        <p class="text-sm font-bold text-stone-800">🏦 Mã QR Ngân Hàng</p>
                                        <p class="text-[11px] text-stone-500 mt-0.5 leading-snug">Quét mã VietQR 24/7 tự động điền số tiền & nội dung</p>
                                    </div>
                                </label>

                                <label data-payment-card="MOMO_QR" class="p-4 rounded-2xl border-2 transition flex items-start gap-3 cursor-pointer {{ $selectedPaymentMethod === 'MOMO_QR' ? 'border-[#A50064] bg-pink-50/50 shadow-xs' : 'border-sage-200 bg-[#F5FAF7] hover:bg-sage-50/40' }}">
                                    <input type="radio" name="payment_method" value="MOMO_QR" form="checkout-form" {{ $selectedPaymentMethod === 'MOMO_QR' ? 'checked' : '' }} onchange="switchPaymentMethod('MOMO_QR')" class="mt-1 accent-[#A50064]">
                                    <div>
                                        <p class="text-sm font-bold text-stone-800">📱 Mã QR Ví MoMo</p>
                                        <p class="text-[11px] text-stone-500 mt-0.5 leading-snug">Quét mã QR bằng ứng dụng Ví điện tử MoMo tức thì</p>
                                    </div>
                                </label>
                            </div>

                            <!-- PANEL A: BANK QR CODE (VietQR) -->
                            <div id="payment-panel-bank" class="{{ $selectedPaymentMethod === 'BANK_TRANSFER' ? 'block' : 'hidden' }} rounded-3xl border-2 border-blue-200 bg-gradient-to-br from-blue-50/70 via-white to-blue-50/40 p-4 sm:p-5 space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-blue-100 pb-3">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-blue-600 block">Chuyển Khoản Nhanh 24/7 (VietQR Napas)</span>
                                        <h3 class="font-serif text-base font-bold text-stone-800">Quét Mã QR Ngân Hàng Để Thanh Toán</h3>
                                    </div>
                                    <!-- Cho phép đổi ngân hàng nhận nếu khách muốn -->
                                    <div class="inline-flex rounded-xl bg-white p-1 border border-blue-200 text-xs font-semibold">
                                        <button type="button" onclick="selectBankAccount('VCB', 'Vietcombank (VCB)', '1029384756', 'SHINE BEAUTY COSMETICS', this)"
                                            class="bank-tab-btn px-2.5 py-1 rounded-lg bg-blue-600 text-white transition">
                                            Vietcombank
                                        </button>
                                        <button type="button" onclick="selectBankAccount('MB', 'MB Bank (Quân Đội)', '8888686899', 'SHINE BEAUTY COSMETICS', this)"
                                            class="bank-tab-btn px-2.5 py-1 rounded-lg text-stone-600 hover:text-blue-600 transition">
                                            MB Bank
                                        </button>
                                        <button type="button" onclick="selectBankAccount('TCB', 'Techcombank (TCB)', '19038889996868', 'SHINE BEAUTY COSMETICS', this)"
                                            class="bank-tab-btn px-2.5 py-1 rounded-lg text-stone-600 hover:text-blue-600 transition">
                                            Techcombank
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 items-center">
                                    <!-- QR Image Box -->
                                    <div class="flex flex-col items-center justify-center bg-white p-4 rounded-2xl border border-blue-200 shadow-xs">
                                        <img id="bank-qr-img"
                                             src="https://img.vietqr.io/image/VCB-1029384756-compact2.png?amount={{ $totalAmount }}&addInfo={{ urlencode('SHINE ' . (old('phone', auth()->user()->phone ?? 'THANHTOAN'))) }}&accountName={{ urlencode('SHINE BEAUTY COSMETICS') }}"
                                             onerror="this.onerror=null;this.src='https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=8&data=' + encodeURIComponent('BANK:VCB|ACC:1029384756|NAME:SHINE BEAUTY COSMETICS|AMOUNT:{{ $totalAmount }}');"
                                             alt="Mã QR Chuyển Khoản Ngân Hàng"
                                             class="w-48 h-48 object-contain rounded-xl">
                                        <p class="text-[11px] font-semibold text-blue-700 mt-2 text-center">
                                            📷 Mở App Ngân hàng bất kỳ để quét mã
                                        </p>
                                    </div>

                                    <!-- Bank Info Details -->
                                    <div class="space-y-2.5 text-xs">
                                        <div class="bg-white p-3 rounded-2xl border border-blue-100">
                                            <span class="text-stone-400 block">Ngân hàng thụ hưởng:</span>
                                            <strong id="bank-info-name" class="text-stone-800 text-sm">Vietcombank (VCB)</strong>
                                        </div>

                                        <div class="bg-white p-3 rounded-2xl border border-blue-100 flex items-center justify-between gap-2">
                                            <div>
                                                <span class="text-stone-400 block">Số tài khoản nhận:</span>
                                                <strong id="bank-info-acc" class="font-mono text-base text-blue-700">1029384756</strong>
                                                <span id="bank-info-owner" class="block text-[11px] text-stone-500 font-medium">Chủ TK: SHINE BEAUTY COSMETICS</span>
                                            </div>
                                            <button type="button" onclick="copyText(document.getElementById('bank-info-acc').textContent, this)"
                                                class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] flex-shrink-0">
                                                Sao chép STK
                                            </button>
                                        </div>

                                        <div class="bg-white p-3 rounded-2xl border border-blue-100 flex items-center justify-between gap-2">
                                            <div>
                                                <span class="text-stone-400 block">Số tiền cần chuyển:</span>
                                                <strong id="bank-qr-amount-text" class="text-base text-emerald-600">{{ number_format($totalAmount, 0, ',', '.') }}₫</strong>
                                            </div>
                                            <button type="button" onclick="copyText(String(checkoutTotalAmount), this)"
                                                class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] flex-shrink-0">
                                                Chép số tiền
                                            </button>
                                        </div>

                                        <div class="bg-white p-3 rounded-2xl border border-blue-100 flex items-center justify-between gap-2">
                                            <div>
                                                <span class="text-stone-400 block">Nội dung chuyển khoản:</span>
                                                <strong id="bank-transfer-note" class="font-mono text-sm text-stone-900">SHINE {{ preg_replace('/\s+/', '', old('phone', auth()->user()->phone ?? 'SDT')) }}</strong>
                                            </div>
                                            <button type="button" onclick="copyText(document.getElementById('bank-transfer-note').textContent, this)"
                                                class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] flex-shrink-0">
                                                Chép nội dung
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-[11px] text-blue-800 bg-blue-100/70 px-3.5 py-2 rounded-xl">
                                    💡 <strong>Lưu ý xác thực:</strong> Sau khi bấm <strong>"Hoàn Tất Đặt Hàng"</strong>, đơn hàng sẽ ở trạng thái <strong>Đơn hàng chưa thanh toán thành công</strong> cho đến khi hệ thống xác thực bạn đã chuyển khoản đúng số tiền qua mã QR.
                                </p>
                            </div>

                            <!-- PANEL B: MOMO QR CODE -->
                            <div id="payment-panel-momo" class="{{ $selectedPaymentMethod === 'MOMO_QR' ? 'block' : 'hidden' }} rounded-3xl border-2 border-pink-300 bg-gradient-to-br from-[#FDF2F8] via-white to-pink-50/60 p-4 sm:p-5 space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-pink-200 pb-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-[#A50064] text-white font-bold flex items-center justify-center text-xs shadow-xs">
                                            MoMo
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-bold uppercase tracking-widest text-[#A50064] block">Thanh Toán Ví Điện Tử MoMo</span>
                                            <h3 class="font-serif text-base font-bold text-stone-800">Quét Mã QR MoMo Để Thanh Toán</h3>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold bg-[#A50064] text-white px-3 py-1 rounded-full">Cần Xác Thực Chuyển Khoản</span>
                                </div>

                                <div class="grid grid-cols-1 gap-4 items-center">
                                    <!-- MoMo QR Image Box -->
                                    <div class="flex flex-col items-center justify-center bg-white p-4 rounded-2xl border-2 border-pink-200 shadow-xs">
                                        <div class="relative p-2 rounded-2xl bg-white border border-pink-100">
                                            <img id="momo-qr-img"
                                                 src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&color=A50064&bgcolor=FFFFFF&margin=6&data={{ urlencode('2|99|0901000888|SHINE BEAUTY COSMETICS|admin@luminabeauty.vn|0|0|' . $totalAmount . '|SHINE ' . preg_replace('/\s+/', '', old('phone', auth()->user()->phone ?? 'SDT')) . '|transfer_myqr') }}"
                                                 alt="Mã QR Ví MoMo"
                                                 class="w-44 h-44 object-contain rounded-xl">
                                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                <span class="bg-[#A50064] text-white text-[10px] font-extrabold px-2 py-1 rounded-lg shadow-sm border-2 border-white">
                                                    MoMo
                                                </span>
                                            </div>
                                        </div>
                                        <p class="text-[11px] font-semibold text-[#A50064] mt-2 text-center">
                                            📱 Mở ứng dụng MoMo → Chọn "Quét Mã"
                                        </p>
                                    </div>

                                    <!-- MoMo Wallet Details -->
                                    <div class="space-y-2.5 text-xs">
                                        <div class="bg-white p-3 rounded-2xl border border-pink-100">
                                            <span class="text-stone-400 block">Chủ tài khoản Ví MoMo:</span>
                                            <strong class="text-stone-800 text-sm">SHINE BEAUTY COSMETICS</strong>
                                        </div>

                                        <div class="bg-white p-3 rounded-2xl border border-pink-100 flex items-center justify-between gap-2">
                                            <div>
                                                <span class="text-stone-400 block">Số điện thoại nhận tiền MoMo:</span>
                                                <strong id="momo-phone-acc" class="font-mono text-base text-[#A50064]">0901000888</strong>
                                            </div>
                                            <button type="button" onclick="copyText('0901000888', this)"
                                                class="px-3 py-1.5 rounded-xl bg-pink-50 hover:bg-pink-100 text-[#A50064] font-bold text-[11px] flex-shrink-0">
                                                Sao chép SĐT
                                            </button>
                                        </div>

                                        <div class="bg-white p-3 rounded-2xl border border-pink-100 flex items-center justify-between gap-2">
                                            <div>
                                                <span class="text-stone-400 block">Số tiền thanh toán:</span>
                                                <strong id="momo-qr-amount-text" class="text-base text-[#A50064]">{{ number_format($totalAmount, 0, ',', '.') }}₫</strong>
                                            </div>
                                            <button type="button" onclick="copyText(String(checkoutTotalAmount), this)"
                                                class="px-3 py-1.5 rounded-xl bg-pink-50 hover:bg-pink-100 text-[#A50064] font-bold text-[11px] flex-shrink-0">
                                                Chép số tiền
                                            </button>
                                        </div>

                                        <div class="bg-white p-3 rounded-2xl border border-pink-100 flex items-center justify-between gap-2">
                                            <div>
                                                <span class="text-stone-400 block">Lời nhắn chuyển tiền MoMo:</span>
                                                <strong id="momo-transfer-note" class="font-mono text-sm text-stone-900">SHINE {{ preg_replace('/\s+/', '', old('phone', auth()->user()->phone ?? 'SDT')) }}</strong>
                                            </div>
                                            <button type="button" onclick="copyText(document.getElementById('momo-transfer-note').textContent, this)"
                                                class="px-3 py-1.5 rounded-xl bg-pink-50 hover:bg-pink-100 text-[#A50064] font-bold text-[11px] flex-shrink-0">
                                                Chép lời nhắn
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-[11px] text-[#A50064] bg-pink-100/70 px-3.5 py-2 rounded-xl">
                                    💡 <strong>Lưu ý xác thực:</strong> Sau khi nhấn <strong>"Hoàn Tất Đặt Hàng"</strong>, đơn hàng sẽ ở trạng thái <strong>Đơn hàng chưa thanh toán thành công</strong> cho đến khi giao dịch chuyển khoản Ví MoMo qua mã QR được xác thực thành công.
                                </p>
                            </div>

                            <button type="submit" id="checkout-submit-btn" form="checkout-form" class="w-full bg-sage-500 hover:bg-sage-600 text-white font-semibold py-4 px-8 rounded-full shadow-lg shadow-sage-200 transition text-base cursor-pointer">
                                Hoàn Tất Đặt Hàng (<span id="submit-btn-total-text">{{ number_format($totalAmount, 0, ',', '.') }}₫</span>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<script>
    // Sao chép nhanh STK / Số tiền / Nội dung chuyển khoản
    function copyText(text, btn) {
        if (!navigator.clipboard) {
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
        } else {
            navigator.clipboard.writeText(text);
        }
        if (btn) {
            const orig = btn.textContent;
            btn.textContent = '✓ Đã chép';
            setTimeout(() => { btn.textContent = orig; }, 1600);
        }
    }

    function fillVerifiedTransferSample(amount, content) {
        const amountEl = document.getElementById('verify-transfer-amount');
        const contentEl = document.getElementById('verify-transfer-content');
        if (amountEl) amountEl.value = amount;
        if (contentEl) contentEl.value = content;
    }

    async function checkQrPaymentLive(checkUrl) {
        const resultBox = document.getElementById('qr-live-check-result');
        if (!resultBox) return;

        resultBox.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-300', 'text-emerald-800', 'bg-amber-100', 'border-amber-300', 'text-amber-900', 'bg-red-50', 'border-red-300', 'text-red-800');
        resultBox.classList.add('bg-white', 'border-amber-300', 'text-stone-700');
        resultBox.textContent = '⏳ Đang kiểm tra giao dịch chuyển khoản trên hệ thống...';

        try {
            const res = await fetch(checkUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();

            resultBox.classList.remove('bg-white', 'text-stone-700');
            if (data.paid) {
                resultBox.classList.add('bg-emerald-50', 'border-emerald-300', 'text-emerald-800');
                resultBox.textContent = '✓ ' + data.message;
                setTimeout(() => window.location.reload(), 900);
            } else {
                resultBox.classList.add('bg-amber-100', 'border-amber-300', 'text-amber-900');
                resultBox.textContent = '⏳ ' + (data.message || 'Đơn hàng chưa thanh toán thành công.');
            }
        } catch (err) {
            resultBox.classList.remove('bg-white', 'text-stone-700');
            resultBox.classList.add('bg-red-50', 'border-red-300', 'text-red-800');
            resultBox.textContent = '⚠ Không thể kiểm tra trạng thái thanh toán lúc này. Vui lòng thử lại.';
        }
    }

    // Chuyển đổi phương thức thanh toán (COD / BANK_TRANSFER / MOMO_QR)
    let checkoutTotalAmount = {{ (int) $totalAmount }};
    const checkoutAfterDiscount = {{ (int) $afterDiscount }};
    const freeShippingThreshold = {{ (int) $freeShippingThreshold }};
    let currentBankCode = 'VCB';
    let currentBankAcc = '1029384756';
    let currentBankOwner = 'SHINE BEAUTY COSMETICS';

    function switchPaymentMethod(method) {
        const bankPanel = document.getElementById('payment-panel-bank');
        const momoPanel = document.getElementById('payment-panel-momo');

        if (bankPanel) bankPanel.classList.toggle('hidden', method !== 'BANK_TRANSFER');
        if (momoPanel) momoPanel.classList.toggle('hidden', method !== 'MOMO_QR');

        document.querySelectorAll('[data-payment-card]').forEach(card => {
            const cardMethod = card.getAttribute('data-payment-card');
            card.className = 'p-4 rounded-2xl border-2 transition flex items-start gap-3 cursor-pointer ';
            if (cardMethod === method) {
                if (method === 'BANK_TRANSFER') {
                    card.className += 'border-blue-500 bg-blue-50/50 shadow-xs';
                } else if (method === 'MOMO_QR') {
                    card.className += 'border-[#A50064] bg-pink-50/50 shadow-xs';
                } else {
                    card.className += 'border-sage-500 bg-sage-50/70 shadow-xs';
                }
            } else {
                card.className += 'border-sage-200 bg-[#F5FAF7] hover:bg-sage-50/40';
            }
        });
    }

    function getTransferContent() {
        const phoneEl = document.getElementById('checkout-phone');
        const cleanPhone = phoneEl ? phoneEl.value.replace(/[^0-9]/g, '') : '';
        return 'SHINE ' + (cleanPhone || 'THANHTOAN');
    }

    function updateDynamicQrCodes() {
        const note = getTransferContent();
        const bankNoteEl = document.getElementById('bank-transfer-note');
        const momoNoteEl = document.getElementById('momo-transfer-note');
        if (bankNoteEl) bankNoteEl.textContent = note;
        if (momoNoteEl) momoNoteEl.textContent = note;

        const bankQrImg = document.getElementById('bank-qr-img');
        if (bankQrImg) {
            bankQrImg.src = `https://img.vietqr.io/image/${currentBankCode}-${currentBankAcc}-compact2.png?amount=${checkoutTotalAmount}&addInfo=${encodeURIComponent(note)}&accountName=${encodeURIComponent(currentBankOwner)}`;
        }

        const momoQrImg = document.getElementById('momo-qr-img');
        if (momoQrImg) {
            const momoPayload = `2|99|0901000888|SHINE BEAUTY COSMETICS|admin@luminabeauty.vn|0|0|${checkoutTotalAmount}|${note}|transfer_myqr`;
            momoQrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=240x240&color=A50064&bgcolor=FFFFFF&margin=6&data=${encodeURIComponent(momoPayload)}`;
        }
    }

    function selectBankAccount(code, bankTitle, accNum, ownerName, btn) {
        currentBankCode = code;
        currentBankAcc = accNum;
        currentBankOwner = ownerName;

        const nameEl = document.getElementById('bank-info-name');
        const accEl = document.getElementById('bank-info-acc');
        const ownerEl = document.getElementById('bank-info-owner');

        if (nameEl) nameEl.textContent = bankTitle;
        if (accEl) accEl.textContent = accNum;
        if (ownerEl) ownerEl.textContent = 'Chủ TK: ' + ownerName;

        document.querySelectorAll('.bank-tab-btn').forEach(b => {
            b.className = 'bank-tab-btn px-2.5 py-1 rounded-lg text-stone-600 hover:text-blue-600 transition';
        });
        if (btn) {
            btn.className = 'bank-tab-btn px-2.5 py-1 rounded-lg bg-blue-600 text-white transition';
        }

        updateDynamicQrCodes();
    }

    // Dữ liệu hành chính Việt Nam (Tỉnh/Thành phố -> Quận/Huyện -> Phường/Xã) + đồng bộ API đầy đủ 63 Tỉnh/TP
    const FALLBACK_VN_LOCATIONS = [
        {
            name: "TP. Hồ Chí Minh",
            districts: [
                { name: "Quận 1", wards: ["Phường Bến Nghé", "Phường Bến Thành", "Phường Cô Giang", "Phường Cầu Kho", "Phường Cầu Ông Lãnh", "Phường Đa Kao", "Phường Nguyễn Cư Trinh", "Phường Nguyễn Thái Bình", "Phường Phạm Ngũ Lão", "Phường Tân Định"] },
                { name: "Quận 3", wards: ["Phường Võ Thị Sáu", "Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14"] },
                { name: "Quận 5", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 11", "Phường 12", "Phường 13", "Phường 14"] },
                { name: "Quận 7", wards: ["Phường Tân Phong", "Phường Tân Phú", "Phường Tân Quy", "Phường Tân Thuận Đông", "Phường Tân Thuận Tây", "Phường Tân Kiểng", "Phường Tân Hưng", "Phường Bình Thuận", "Phường Phú Mỹ", "Phường Phú Thuận"] },
                { name: "Quận 10", wards: ["Phường 1", "Phường 2", "Phường 4", "Phường 6", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"] },
                { name: "Quận Bình Thạnh", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 5", "Phường 6", "Phường 7", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 17", "Phường 19", "Phường 21", "Phường 22", "Phường 24", "Phường 25", "Phường 26", "Phường 27", "Phường 28"] },
                { name: "Quận Gò Vấp", wards: ["Phường 1", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16", "Phường 17"] },
                { name: "Quận Phú Nhuận", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 13", "Phường 15", "Phường 17"] },
                { name: "Quận Tân Bình", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"] },
                { name: "TP. Thủ Đức", wards: ["Phường Thảo Điền", "Phường An Phú", "Phường An Khánh", "Phường Bình Thọ", "Phường Hiệp Bình Chánh", "Phường Hiệp Bình Phước", "Phường Linh Chiểu", "Phường Linh Đông", "Phường Linh Tây", "Phường Linh Trung", "Phường Long Thạnh Mỹ", "Phường Phước Long B"] },
                { name: "Huyện Củ Chi", wards: ["Thị trấn Củ Chi", "Xã Tân An Hội", "Xã Phước Vĩnh An", "Xã Tân Thông Hội", "Xã Tân Phú Trung", "Xã Bình Mỹ", "Xã Hòa Phú", "Xã Trung An"] },
                { name: "Huyện Hóc Môn", wards: ["Thị trấn Hóc Môn", "Xã Bà Điểm", "Xã Đông Thạnh", "Xã Nhị Bình", "Xã Tân Hiệp", "Xã Tân Thới Nhì", "Xã Tân Xuân", "Xã Thới Tam Thôn", "Xã Trung Chánh", "Xã Xuân Thới Đông", "Xã Xuân Thới Sơn", "Xã Xuân Thới Thượng"] },
                { name: "Huyện Bình Chánh", wards: ["Thị trấn Tân Túc", "Xã Bình Hưng", "Xã Phong Phú", "Xã Vĩnh Lộc A", "Xã Vĩnh Lộc B", "Xã Bình Chánh", "Xã Tân Kiên"] },
                { name: "Huyện Nhà Bè", wards: ["Thị trấn Nhà Bè", "Xã Phước Kiển", "Xã Phước Lộc", "Xã Nhơn Đức", "Xã Phú Xuân", "Xã Hiệp Phước"] },
                { name: "Huyện Cần Giờ", wards: ["Thị trấn Cần Thạnh", "Xã Bình Khánh", "Xã Cần Thạnh", "Xã Long Hòa", "Xã Thạnh An"] }
            ]
        },
        {
            name: "Thành phố Hà Nội",
            districts: [
                { name: "Quận Ba Đình", wards: ["Phường Cống Vị", "Phường Điện Biên", "Phường Đội Cấn", "Phường Giảng Võ", "Phường Kim Mã", "Phường Liễu Giai", "Phường Ngọc Hà", "Phường Ngọc Khánh", "Phường Nguyễn Trung Trực", "Phường Phúc Xá", "Phường Quán Thánh", "Phường Thành Công", "Phường Trúc Bạch", "Phường Vĩnh Phúc"] },
                { name: "Quận Hoàn Kiếm", wards: ["Phường Chương Dương", "Phường Cửa Đông", "Phường Cửa Nam", "Phường Đồng Xuân", "Phường Hàng Bạc", "Phường Hàng Bài", "Phường Hàng Bồ", "Phường Hàng Bông", "Phường Hàng Buồm", "Phường Hàng Đào", "Phường Hàng Gai", "Phường Hàng Mã", "Phường Hàng Trống", "Phường Lý Thái Tổ", "Phường Phan Chu Trinh", "Phường Phúc Tân", "Phường Trần Hưng Đạo", "Phường Tràng Tiền"] },
                { name: "Quận Cầu Giấy", wards: ["Phường Dịch Vọng", "Phường Dịch Vọng Hậu", "Phường Mai Dịch", "Phường Nghĩa Đô", "Phường Nghĩa Tân", "Phường Quan Hoa", "Phường Trung Hòa", "Phường Yên Hòa"] },
                { name: "Quận Đống Đa", wards: ["Phường Cát Linh", "Phường Hàng Bột", "Phường Khâm Thiên", "Phường Khương Thượng", "Phường Kim Liên", "Phường Láng Hạ", "Phường Láng Thượng", "Phường Nam Đồng", "Phường Ngã Tư Sở", "Phường Ô Chợ Dừa", "Phường Phương Liên", "Phường Phương Mai", "Phường Quang Trung", "Phường Trung Liệt", "Phường Trung Phụng", "Phường Trung Tự", "Phường Văn Chương", "Phường Văn Miếu"] },
                { name: "Quận Hai Bà Trưng", wards: ["Phường Bách Khoa", "Phường Bạch Đằng", "Phường Bạch Mai", "Phường Cầu Dền", "Phường Đống Mác", "Phường Đồng Nhân", "Phường Đồng Tâm", "Phường Lê Đại Hành", "Phường Minh Khai", "Phường Nguyễn Du", "Phường Phạm Đình Hổ", "Phường Phố Huế", "Phường Quỳnh Lôi", "Phường Quỳnh Mai", "Phường Thanh Lương", "Phường Thanh Nhàn", "Phường Trương Định", "Phường Vĩnh Tuy"] },
                { name: "Quận Thanh Xuân", wards: ["Phường Hạ Đình", "Phường Khương Đình", "Phường Khương Mai", "Phường Khương Trung", "Phường Kim Giang", "Phường Nhân Chính", "Phường Phương Liệt", "Phường Thanh Xuân Bắc", "Phường Thanh Xuân Nam", "Phường Thanh Xuân Trung", "Phường Thượng Đình"] },
                { name: "Quận Tây Hồ", wards: ["Phường Bưởi", "Phường Nhật Tân", "Phường Phú Thượng", "Phường Quảng An", "Phường Thụy Khuê", "Phường Tứ Liên", "Phường Xuân La", "Phường Yên Phụ"] },
                { name: "Quận Nam Từ Liêm", wards: ["Phường Cầu Diễn", "Phường Đại Mỗ", "Phường Mễ Trì", "Phường Mỹ Đình 1", "Phường Mỹ Đình 2", "Phường Phú Đô", "Phường Phương Canh", "Phường Tây Mỗ", "Phường Trung Văn", "Phường Xuân Phương"] },
                { name: "Huyện Đông Anh", wards: ["Thị trấn Đông Anh", "Xã Hải Bối", "Xã Vĩnh Ngọc", "Xã Uy Nỗ", "Xã Tiên Dương", "Xã Nguyên Khê"] },
                { name: "Huyện Gia Lâm", wards: ["Thị trấn Trâu Quỳ", "Thị trấn Yên Viên", "Xã Đa Tốn", "Xã Kiêu Kỵ", "Xã Bát Tràng"] },
                { name: "Huyện Sóc Sơn", wards: ["Thị trấn Sóc Sơn", "Xã Phù Linh", "Xã Phú Minh", "Xã Quang Tiến", "Xã Mai Đình"] }
            ]
        },
        {
            name: "Thành phố Đà Nẵng",
            districts: [
                { name: "Quận Hải Châu", wards: ["Phường Bình Hiên", "Phường Bình Thuận", "Phường Hải Châu I", "Phường Hải Châu II", "Phường Hòa Cường Bắc", "Phường Hòa Cường Nam", "Phường Hòa Thuận Đông", "Phường Hòa Thuận Tây", "Phường Nam Dương", "Phường Phước Ninh", "Phường Thạch Thang", "Phường Thanh Bình", "Phường Thuận Phước"] },
                { name: "Quận Thanh Khê", wards: ["Phường An Khê", "Phường Chính Gián", "Phường Hòa Khê", "Phường Tam Thuận", "Phường Tân Chính", "Phường Thạc Gián", "Phường Thanh Khê Đông", "Phường Thanh Khê Tây", "Phường Vĩnh Trung", "Phường Xuân Hà"] },
                { name: "Quận Sơn Trà", wards: ["Phường An Hải Bắc", "Phường An Hải Đông", "Phường An Hải Tây", "Phường Mân Thái", "Phường Nại Hiên Đông", "Phường Phước Mỹ", "Phường Thọ Quang"] },
                { name: "Quận Ngũ Hành Sơn", wards: ["Phường Hòa Hải", "Phường Hòa Quý", "Phường Khuê Mỹ", "Phường Mỹ An"] },
                { name: "Quận Liên Chiểu", wards: ["Phường Hòa Hiệp Bắc", "Phường Hòa Hiệp Nam", "Phường Hòa Khánh Bắc", "Phường Hòa Khánh Nam", "Phường Hòa Minh"] }
            ]
        },
        {
            name: "Thành phố Cần Thơ",
            districts: [
                { name: "Quận Ninh Kiều", wards: ["Phường An Bình", "Phường An Cư", "Phường An Hòa", "Phường An Khánh", "Phường An Nghiệp", "Phường An Phú", "Phường Cái Khế", "Phường Hưng Lợi", "Phường Tân An", "Phường Thới Bình", "Phường Xuân Khánh"] },
                { name: "Quận Bình Thủy", wards: ["Phường An Thới", "Phường Bình Thủy", "Phường Bùi Hữu Nghĩa", "Phường Long Hòa", "Phường Long Tuyền", "Phường Thới An Đông", "Phường Trà An", "Phường Trà Nóc"] },
                { name: "Quận Cái Răng", wards: ["Phường Ba Láng", "Phường Hưng Phú", "Phường Hưng Thạnh", "Phường Lê Bình", "Phường Phú Thứ", "Phường Tân Phú", "Phường Thường Thạnh"] }
            ]
        },
        {
            name: "Thành phố Hải Phòng",
            districts: [
                { name: "Quận Hồng Bàng", wards: ["Phường Hoàng Văn Thụ", "Phường Hùng Vương", "Phường Minh Khai", "Phường Phan Bội Châu", "Phường Quán Toan", "Phường Sở Dầu", "Phường Thượng Lý", "Phường Hạ Lý", "Phường Trại Chuối"] },
                { name: "Quận Lê Chân", wards: ["Phường An Biên", "Phường An Dương", "Phường Cát Dài", "Phường Đông Hải", "Phường Dư Hàng", "Phường Dư Hàng Kênh", "Phường Hàng Kênh", "Phường Hồ Nam", "Phường Kênh Dương", "Phường Lam Sơn", "Phường Nghĩa Xá", "Phường Niệm Nghĩa", "Phường Trại Cau", "Phường Trần Nguyên Hãn", "Phường Vĩnh Niệm"] },
                { name: "Quận Ngô Quyền", wards: ["Phường Cầu Đất", "Phường Cầu Tre", "Phường Đằng Giang", "Phường Đông Khê", "Phường Đổng Quốc Bình", "Phường Gia Viên", "Phường Lạch Tray", "Phường Lạc Viên", "Phường Máy Chai", "Phường Máy Tơ", "Phường Vạn Mỹ"] }
            ]
        },
        {
            name: "Tỉnh Bình Dương",
            districts: [
                { name: "TP. Thủ Dầu Một", wards: ["Phường Chánh Nghĩa", "Phường Hiệp Thành", "Phường Phú Cường", "Phường Phú Hòa", "Phường Phú Lợi", "Phường Phú Mỹ", "Phường Phú Tân", "Phường Phú Thọ"] },
                { name: "TP. Dĩ An", wards: ["Phường An Bình", "Phường Bình An", "Phường Bình Thắng", "Phường Dĩ An", "Phường Đông Hòa", "Phường Tân Bình", "Phường Tân Đông Hiệp"] },
                { name: "TP. Thuận An", wards: ["Phường An Phú", "Phường An Thạnh", "Phường Bình Chuẩn", "Phường Bình Hòa", "Phường Bình Nhâm", "Phường Hưng Định", "Phường Lái Thiêu", "Phường Thuận Giao", "Phường Vĩnh Phú"] }
            ]
        },
        {
            name: "Tỉnh Đồng Nai",
            districts: [
                { name: "TP. Biên Hòa", wards: ["Phường An Bình", "Phường Bửu Hòa", "Phường Bửu Long", "Phường Hố Nai", "Phường Hòa Bình", "Phường Long Bình", "Phường Long Bình Tân", "Phường Quang Vinh", "Phường Quyết Thắng", "Phường Tam Hiệp", "Phường Tam Hòa", "Phường Tân Biên", "Phường Tân Hiệp", "Phường Tân Hòa", "Phường Tân Mai", "Phường Tân Phong", "Phường Tân Tiến", "Phường Thống Nhất", "Phường Trảng Dài", "Phường Trung Dũng"] },
                { name: "TP. Long Khánh", wards: ["Phường Bảo Vinh", "Phường Bàu Sen", "Phường Phú Bình", "Phường Suối Tre", "Phường Xuân An", "Phường Xuân Bình", "Phường Xuân Hòa", "Phường Xuân Lập", "Phường Xuân Tân", "Phường Xuân Thanh", "Phường Xuân Trung"] }
            ]
        },
        {
            name: "Tỉnh Khánh Hòa",
            districts: [
                { name: "TP. Nha Trang", wards: ["Phường Lộc Thọ", "Phường Ngọc Hiệp", "Phường Phước Hải", "Phường Phước Hòa", "Phường Phước Long", "Phường Phước Tân", "Phường Phước Tiến", "Phường Phương Sài", "Phường Phương Sơn", "Phường Tân Lập", "Phường Vạn Thạnh", "Phường Vạn Thắng", "Phường Vĩnh Hải", "Phường Vĩnh Hòa", "Phường Vĩnh Nguyên", "Phường Vĩnh Phước", "Phường Vĩnh Thọ", "Phường Vĩnh Trường", "Phường Xương Huân"] },
                { name: "TP. Cam Ranh", wards: ["Phường Ba Ngòi", "Phường Cam Linh", "Phường Cam Lộc", "Phường Cam Lợi", "Phường Cam Nghĩa", "Phường Cam Phú", "Phường Cam Phúc Bắc", "Phường Cam Phúc Nam", "Phường Cam Thuận"] }
            ]
        },
        {
            name: "Tỉnh Lâm Đồng",
            districts: [
                { name: "TP. Đà Lạt", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12"] },
                { name: "TP. Bảo Lộc", wards: ["Phường 1", "Phường 2", "Phường B'Lao", "Phường Lộc Phát", "Phường Lộc Sơn", "Phường Lộc Tiến"] }
            ]
        },
        {
            name: "Tỉnh Bà Rịa - Vũng Tàu",
            districts: [
                { name: "TP. Vũng Tàu", wards: ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường Nguyễn An Ninh", "Phường Rạch Dừa", "Phường Thắng Nhất", "Phường Thắng Nhì", "Phường Thắng Tam"] },
                { name: "TP. Bà Rịa", wards: ["Phường Kim Dinh", "Phường Long Hương", "Phường Long Tâm", "Phường Long Toàn", "Phường Phước Hiệp", "Phường Phước Hưng", "Phường Phước Nguyên", "Phường Phước Trung"] }
            ]
        },
        {
            name: "Tỉnh Thừa Thiên Huế",
            districts: [
                { name: "TP. Huế", wards: ["Phường An Cựu", "Phường An Đông", "Phường An Hòa", "Phường An Tây", "Phường Đông Ba", "Phường Gia Hội", "Phường Hương Sơ", "Phường Kim Long", "Phường Phú Hội", "Phường Phú Hậu", "Phường Phú Nhuận", "Phường Phước Vĩnh", "Phường Phường Đúc", "Phường Tây Lộc", "Phường Thuận An", "Phường Thuận Hòa", "Phường Thuận Lộc", "Phường Trường An", "Phường Vĩnh Ninh", "Phường Vỹ Dạ", "Phường Xuân Phú"] }
            ]
        },
        {
            name: "Tỉnh Nghệ An",
            districts: [
                { name: "TP. Vinh", wards: ["Phường Bến Thủy", "Phường Cửa Nam", "Phường Đội Cung", "Phường Đông Vĩnh", "Phường Hà Huy Tập", "Phường Hồng Sơn", "Phường Hưng Bình", "Phường Hưng Dũng", "Phường Hưng Phúc", "Phường Lê Lợi", "Phường Lê Mao", "Phường Quán Bàu", "Phường Quang Trung", "Phường Trung Đô", "Phường Trường Thi", "Phường Vinh Tân"] }
            ]
        },
        {
            name: "Tỉnh Thanh Hóa",
            districts: [
                { name: "TP. Thanh Hóa", wards: ["Phường An Hưng", "Phường Ba Đình", "Phường Điện Biên", "Phường Đông Cương", "Phường Đông Hải", "Phường Đông Hương", "Phường Đông Lĩnh", "Phường Đông Sơn", "Phường Đông Tân", "Phường Đông Thọ", "Phường Đông Vệ", "Phường Hàm Rồng", "Phường Lam Sơn", "Phường Nam Ngạn", "Phường Ngọc Trạo", "Phường Phú Sơn", "Phường Quảng Hưng", "Phường Quảng Thành", "Phường Quảng Thắng", "Phường Tân Sơn", "Phường Trường Thi"] }
            ]
        },
        {
            name: "Tỉnh Quảng Ninh",
            districts: [
                { name: "TP. Hạ Long", wards: ["Phường Bạch Đằng", "Phường Bãi Cháy", "Phường Cao Thắng", "Phường Cao Xanh", "Phường Đại Yên", "Phường Giếng Đáy", "Phường Hà Khánh", "Phường Hà Khẩu", "Phường Hà Lầm", "Phường Hà Phong", "Phường Hà Trung", "Phường Hà Tu", "Phường Hồng Gai", "Phường Hồng Hà", "Phường Hồng Hải", "Phường Hùng Thắng", "Phường Trần Hưng Đạo", "Phường Tuần Châu", "Phường Việt Hưng", "Phường Yết Kiêu"] }
            ]
        },
        {
            name: "Tỉnh Lào Cai",
            districts: [
                { name: "Thị xã Sa Pa", wards: ["Phường Sa Pa", "Phường Phan Si Păng", "Phường Hàm Rồng", "Phường Cầu Mây", "Phường Ô Quý Hồ", "Xã Tả Van", "Xã Tả Phìn"] },
                { name: "TP. Lào Cai", wards: ["Phường Cốc Lếu", "Phường Kim Tân", "Phường Bắc Lệnh", "Phường Lào Cai", "Phường Phố Mới"] }
            ]
        },
        {
            name: "Tỉnh Hà Giang",
            districts: [
                { name: "TP. Hà Giang", wards: ["Phường Minh Khai", "Phường Nguyễn Trãi", "Phường Trần Phú", "Phường Quang Trung", "Phường Ngọc Hà"] },
                { name: "Huyện Đồng Văn", wards: ["Thị trấn Đồng Văn", "Thị trấn Phó Bảng", "Xã Lũng Cú", "Xã Sủng Là"] }
            ]
        },
        {
            name: "Tỉnh Cà Mau",
            districts: [
                { name: "TP. Cà Mau", wards: ["Phường 1", "Phường 2", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9"] },
                { name: "Huyện Năm Căn", wards: ["Thị trấn Năm Căn", "Xã Đất Mới", "Xã Hàm Rồng", "Xã Hàng Vịnh"] }
            ]
        },
        {
            name: "Tỉnh Kiên Giang",
            districts: [
                { name: "TP. Phú Quốc", wards: ["Phường Dương Đông", "Phường An Thới", "Xã Cửa Cạn", "Xã Gành Dầu", "Xã Hàm Ninh", "Xã Bãi Thơm"] },
                { name: "TP. Rạch Giá", wards: ["Phường Vĩnh Bảo", "Phường Vĩnh Thanh", "Phường Vĩnh Thanh Vân", "Phường Vĩnh Lạc"] }
            ]
        }
    ];

    document.addEventListener('DOMContentLoaded', function () {
        const provinceSelect = document.getElementById('select-province');
        const districtSelect = document.getElementById('select-district');
        const wardSelect = document.getElementById('select-ward');
        const streetInput = document.getElementById('input-street');
        const fullAddressInput = document.getElementById('input-full-address');
        const addressPreviewText = document.getElementById('address-preview-text');
        const phoneInput = document.getElementById('checkout-phone');

        if (phoneInput) {
            phoneInput.addEventListener('input', updateDynamicQrCodes);
        }

        if (!provinceSelect || !districtSelect || !wardSelect || !streetInput) return;

        let locationsData = [...FALLBACK_VN_LOCATIONS];

        function updateFullAddressPreview() {
            const street = streetInput.value.trim();
            const ward = wardSelect.value.trim();
            const district = districtSelect.value.trim();
            const province = provinceSelect.value.trim();

            const parts = [street, ward, district, province].filter(Boolean);
            const combined = parts.join(', ');

            if (fullAddressInput) {
                fullAddressInput.value = combined;
            }
            if (addressPreviewText) {
                addressPreviewText.textContent = combined || 'Vui lòng chọn Tỉnh/Thành phố, Quận/Huyện, Phường/Xã và nhập số nhà, tên đường.';
            }
        }

        function populateProvinces(selectedProvince = '') {
            provinceSelect.innerHTML = '<option value="">-- Chọn Tỉnh/Thành phố --</option>';
            locationsData.forEach(prov => {
                const opt = document.createElement('option');
                opt.value = prov.name;
                opt.textContent = prov.name;
                if (selectedProvince && prov.name === selectedProvince) {
                    opt.selected = true;
                }
                provinceSelect.appendChild(opt);
            });
        }

        function populateDistricts(provinceName, selectedDistrict = '') {
            districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            wardSelect.disabled = true;

            const prov = locationsData.find(p => p.name === provinceName);
            if (!prov || !prov.districts || prov.districts.length === 0) {
                districtSelect.disabled = true;
                return;
            }

            districtSelect.disabled = false;
            prov.districts.forEach(dist => {
                const opt = document.createElement('option');
                opt.value = dist.name;
                opt.textContent = dist.name;
                if (selectedDistrict && dist.name === selectedDistrict) {
                    opt.selected = true;
                }
                districtSelect.appendChild(opt);
            });
        }

        function populateWards(provinceName, districtName, selectedWard = '') {
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            const prov = locationsData.find(p => p.name === provinceName);
            if (!prov) {
                wardSelect.disabled = true;
                return;
            }
            const dist = (prov.districts || []).find(d => d.name === districtName);
            if (!dist || !dist.wards || dist.wards.length === 0) {
                wardSelect.disabled = true;
                return;
            }

            wardSelect.disabled = false;
            dist.wards.forEach(w => {
                const wardName = typeof w === 'string' ? w : w.name;
                const opt = document.createElement('option');
                opt.value = wardName;
                opt.textContent = wardName;
                if (selectedWard && wardName === selectedWard) {
                    opt.selected = true;
                }
                wardSelect.appendChild(opt);
            });
        }

        const oldProv = provinceSelect.getAttribute('data-old') || '';
        const oldDist = districtSelect.getAttribute('data-old') || '';
        const oldWard = wardSelect.getAttribute('data-old') || '';

        populateProvinces(oldProv);
        if (oldProv) {
            populateDistricts(oldProv, oldDist);
        }
        if (oldProv && oldDist) {
            populateWards(oldProv, oldDist, oldWard);
        }
        updateFullAddressPreview();

        // Tải bổ sung toàn bộ 63 Tỉnh/Thành phố từ Open API nếu có kết nối mạng
        fetch('https://provinces.open-api.vn/api/?depth=3')
            .then(res => res.ok ? res.json() : null)
            .then(apiData => {
                if (!Array.isArray(apiData) || apiData.length === 0) return;
                const currentProv = provinceSelect.value || oldProv;
                const currentDist = districtSelect.value || oldDist;
                const currentWard = wardSelect.value || oldWard;

                locationsData = apiData.map(p => ({
                    name: p.name,
                    districts: (p.districts || []).map(d => ({
                        name: d.name,
                        wards: (d.wards || []).map(w => w.name)
                    }))
                }));

                populateProvinces(currentProv);
                if (currentProv) {
                    populateDistricts(currentProv, currentDist);
                }
                if (currentProv && currentDist) {
                    populateWards(currentProv, currentDist, currentWard);
                }
                updateFullAddressPreview();
            })
            .catch(() => {
                // Giữ nguyên dữ liệu FALLBACK_VN_LOCATIONS nếu offline
            });

        function formatVnd(amount) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(amount)) + '₫';
        }

        function computeShippingDetails(province, district, mode) {
            // Đơn hàng từ 900.000đ trở lên được miễn phí ship toàn quốc
            if (checkoutAfterDiscount >= freeShippingThreshold) {
                return {
                    fee: 0,
                    label: 'Miễn phí vận chuyển toàn quốc (Đơn từ 900.000₫)',
                    shortZone: 'Freeship toàn quốc (0₫)'
                };
            }

            const pLower = (province || '').toLowerCase();
            const dLower = (district || '').toLowerCase();

            const isMetro = pLower.includes('hồ chí minh') || pLower.includes('hà nội') || pLower.includes('tp.hcm') || pLower.includes('hcm');

            if (mode === 'FLAT_RATE') {
                if (!pLower || isMetro) {
                    return {
                        fee: 15000,
                        label: 'Phí cố định (Flat Rate) — Nội thành (15.000₫)',
                        shortZone: 'Flat Rate • Nội thành: 15.000₫'
                    };
                }
                return {
                    fee: 35000,
                    label: 'Phí cố định (Flat Rate) — Ngoại tỉnh (35.000₫)',
                    shortZone: 'Flat Rate • Ngoại tỉnh: 35.000₫'
                };
            }

            // ZONE_BASED (Tính theo khu vực)
            if (!pLower) {
                return {
                    fee: 15000,
                    label: 'Tính theo khu vực — Mặc định Nội thành (15.000₫)',
                    shortZone: 'Zone-based • Nội thành: 15.000₫'
                };
            }

            if (isMetro) {
                const suburbanKeywords = [
                    'huyện', 'củ chi', 'hóc môn', 'bình chánh', 'nhà bè', 'cần giờ',
                    'đông anh', 'gia lâm', 'sóc sơn', 'thanh trì', 'thường tín', 'hoài đức',
                    'đan phượng', 'phúc thọ', 'thạch thất', 'quốc oai', 'chương mỹ', 'thanh oai',
                    'mỹ đức', 'ứng hòa', 'phú xuyên', 'ba vì', 'mê linh', 'sơn tây'
                ];
                const isSuburban = suburbanKeywords.some(kw => dLower.includes(kw));
                if (isSuburban) {
                    return {
                        fee: 25000,
                        label: 'Tính theo khu vực — Ngoại thành TP.HCM/Hà Nội (25.000₫)',
                        shortZone: 'Zone-based • Ngoại thành: 25.000₫'
                    };
                }
                return {
                    fee: 15000,
                    label: 'Tính theo khu vực — Nội thành TP.HCM/Hà Nội (15.000₫)',
                    shortZone: 'Zone-based • Nội thành: 15.000₫'
                };
            }

            const remoteKeywords = [
                'hà giang', 'cao bằng', 'bắc kạn', 'lạng sơn', 'tuyên quang', 'lào cai',
                'yên bái', 'điện biên', 'lai châu', 'sơn la', 'hòa bình', 'kon tum',
                'gia lai', 'đắk lắk', 'đắk nông', 'cà mau', 'bạc liêu', 'sóc trăng',
                'kiên giang', 'phú quốc', 'côn đảo', 'bạch long vĩ', 'lý sơn', 'phú quý', 'cồn cỏ'
            ];
            const isRemote = remoteKeywords.some(kw => pLower.includes(kw) || dLower.includes(kw));
            if (isRemote) {
                return {
                    fee: 50000,
                    label: 'Tính theo khu vực — Vùng sâu vùng xa / Hải đảo (50.000₫)',
                    shortZone: 'Zone-based • Vùng sâu vùng xa: 50.000₫'
                };
            }

            return {
                fee: 35000,
                label: 'Tính theo khu vực — Liên tỉnh (35.000₫)',
                shortZone: 'Zone-based • Liên tỉnh: 35.000₫'
            };
        }

        window.recalculateShippingAndTotal = function () {
            const selectedModeEl = document.querySelector('input[name="shipping_mode"]:checked');
            const mode = selectedModeEl ? selectedModeEl.value : 'ZONE_BASED';

            // Highlight card của phương thức tính phí đang chọn
            document.querySelectorAll('[data-shipping-mode-card]').forEach(card => {
                const cardMode = card.getAttribute('data-shipping-mode-card');
                if (cardMode === mode) {
                    card.className = 'p-3.5 rounded-xl border-2 transition cursor-pointer flex items-start gap-2.5 border-amber-500 bg-white shadow-xs';
                } else {
                    card.className = 'p-3.5 rounded-xl border-2 transition cursor-pointer flex items-start gap-2.5 border-stone-200 bg-white/70 hover:bg-white';
                }
            });

            const prov = provinceSelect ? provinceSelect.value : '';
            const dist = districtSelect ? districtSelect.value : '';
            const details = computeShippingDetails(prov, dist, mode);

            checkoutTotalAmount = checkoutAfterDiscount + details.fee;

            const zoneBadge = document.getElementById('active-shipping-zone-badge');
            if (zoneBadge) zoneBadge.textContent = details.shortZone;

            const boxTitle = document.getElementById('shipping-box-title');
            if (boxTitle && checkoutAfterDiscount < freeShippingThreshold) {
                boxTitle.textContent = `Đơn hàng dưới 900.000₫ — Phí vận chuyển: ${formatVnd(details.fee)}`;
            }

            const summaryZoneSub = document.getElementById('summary-shipping-zone-sub');
            if (summaryZoneSub) summaryZoneSub.textContent = details.label;

            const summaryFeeText = document.getElementById('summary-shipping-fee-text');
            if (summaryFeeText) {
                summaryFeeText.textContent = details.fee > 0 ? formatVnd(details.fee) : 'Miễn phí (0₫)';
            }

            const totalTextEl = document.getElementById('summary-total-amount-text');
            if (totalTextEl) totalTextEl.textContent = formatVnd(checkoutTotalAmount);

            const submitBtnTotal = document.getElementById('submit-btn-total-text');
            if (submitBtnTotal) submitBtnTotal.textContent = formatVnd(checkoutTotalAmount);

            const bankQrAmount = document.getElementById('bank-qr-amount-text');
            if (bankQrAmount) bankQrAmount.textContent = formatVnd(checkoutTotalAmount);

            const momoQrAmount = document.getElementById('momo-qr-amount-text');
            if (momoQrAmount) momoQrAmount.textContent = formatVnd(checkoutTotalAmount);

            updateDynamicQrCodes();
        };

        window.toggleSavedAddressList = function (forceOpen) {
            const listEl = document.getElementById('saved-address-list');
            const btnEl = document.getElementById('toggle-address-list-btn');
            if (!listEl) return;

            const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : listEl.classList.contains('hidden');
            listEl.classList.toggle('hidden', !shouldOpen);
            if (btnEl) {
                const count = document.querySelectorAll('[data-saved-addr-card]').length;
                btnEl.textContent = shouldOpen ? '✕ Đóng danh sách' : `🔄 Thay đổi địa chỉ (${count})`;
            }
        };

        window.applySavedCustomerAddress = function (addrObj, cardEl) {
            if (!addrObj) return;

            const nameEl = document.getElementById('checkout-customer-name');
            if (nameEl && addrObj.recipient_name) {
                nameEl.value = addrObj.recipient_name;
            }
            if (phoneInput && addrObj.recipient_phone) {
                phoneInput.value = addrObj.recipient_phone;
            }

            const provName = addrObj.province || 'TP. Hồ Chí Minh';
            const distName = addrObj.district || 'Quận 1';
            const wardName = addrObj.ward || 'Phường Bến Thành';
            const streetVal = addrObj.street_address || addrObj.address || '';

            const matchedProv = selectOrInjectOption(provinceSelect, provName);
            populateDistricts(matchedProv);
            const matchedDist = selectOrInjectOption(districtSelect, distName);
            populateWards(matchedProv, matchedDist);
            selectOrInjectOption(wardSelect, wardName);

            streetInput.value = streetVal;
            updateFullAddressPreview();
            if (fullAddressInput && addrObj.address) {
                fullAddressInput.value = addrObj.address;
            }
            if (addressPreviewText && addrObj.address) {
                addressPreviewText.textContent = addrObj.address;
            }

            const activeLabel = document.getElementById('active-addr-label');
            const activeDefaultBadge = document.getElementById('active-addr-default-badge');
            const activeRecipient = document.getElementById('active-addr-recipient');
            const activeFull = document.getElementById('active-addr-full');
            if (activeLabel) activeLabel.textContent = `🏷️ ${addrObj.label || 'Địa chỉ'}`;
            if (activeDefaultBadge) activeDefaultBadge.classList.toggle('hidden', !addrObj.is_default);
            if (activeRecipient) {
                const rName = addrObj.recipient_name || (nameEl ? nameEl.value : '');
                const rPhone = addrObj.recipient_phone || (phoneInput ? phoneInput.value : '');
                activeRecipient.textContent = `• ${rName} (${rPhone})`;
            }
            if (activeFull && addrObj.address) activeFull.textContent = addrObj.address;

            document.querySelectorAll('[data-saved-addr-card]').forEach(el => {
                el.className = 'p-3 rounded-xl border-2 transition cursor-pointer flex items-center justify-between gap-3 border-sage-100 bg-white/70 hover:bg-white hover:border-sage-300';
            });
            if (cardEl) {
                cardEl.className = 'p-3 rounded-xl border-2 transition cursor-pointer flex items-center justify-between gap-3 border-sage-500 bg-white shadow-2xs';
            }

            if (document.getElementById('active-saved-address-summary')) {
                window.toggleSavedAddressList(false);
            }

            window.recalculateShippingAndTotal();
        };

        provinceSelect.addEventListener('change', function () {
            populateDistricts(this.value);
            updateFullAddressPreview();
            window.recalculateShippingAndTotal();
        });

        districtSelect.addEventListener('change', function () {
            populateWards(provinceSelect.value, this.value);
            updateFullAddressPreview();
            window.recalculateShippingAndTotal();
        });

        wardSelect.addEventListener('change', updateFullAddressPreview);
        streetInput.addEventListener('input', updateFullAddressPreview);

        function selectOrInjectOption(selectEl, textValue) {
            if (!selectEl || !textValue) return;
            const cleanTarget = textValue.trim().toLowerCase();
            selectEl.disabled = false;

            // Tìm option khớp hoặc gần khớp
            for (let i = 0; i < selectEl.options.length; i++) {
                const optVal = selectEl.options[i].value.trim().toLowerCase();
                if (optVal && (optVal === cleanTarget || optVal.includes(cleanTarget) || cleanTarget.includes(optVal))) {
                    selectEl.selectedIndex = i;
                    return selectEl.options[i].value;
                }
            }

            // Nếu chưa có trong danh sách thì tự động thêm option mới và chọn luôn
            const newOpt = document.createElement('option');
            newOpt.value = textValue.trim();
            newOpt.textContent = textValue.trim();
            newOpt.selected = true;
            selectEl.appendChild(newOpt);
            return newOpt.value;
        }
    });
</script>
@endsection
