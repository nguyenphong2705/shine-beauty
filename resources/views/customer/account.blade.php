@extends('layouts.app')

@php
    $currentTab = $activeSection ?? 'overview';
    $pageTitles = [
        'overview'  => 'Tổng Quan Tài Khoản Khách Hàng | SHINE Beauty',
        'profile'   => 'Đổi Thông Tin Cá Nhân & Xác Thực Email | SHINE Beauty',
        'addresses' => 'Sổ Địa Chỉ Nhận Hàng Mặc Định | SHINE Beauty',
        'cart'      => 'Giỏ Hàng Đã Lưu Của Tôi | SHINE Beauty',
        'orders'    => 'Lịch Sử Đơn Hàng Đã Đặt | SHINE Beauty',
        'vouchers'  => 'Kho Mã Giảm Giá & Voucher Ưu Đãi | SHINE Beauty',
    ];
@endphp

@section('title', $pageTitles[$currentTab] ?? 'Trung Tâm Tài Khoản Khách Hàng | SHINE Beauty')
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-6 sm:space-y-10">
    <!-- Customer Header Banner -->
    <div class="bg-white rounded-3xl border border-sage-100 p-4 sm:p-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-6">
        <div class="flex items-start sm:items-center gap-3.5 sm:gap-4 min-w-0">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-sage-500 to-sage-300 text-white flex items-center justify-center font-serif text-xl sm:text-2xl font-bold shadow-md shadow-sage-200 flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="space-y-1 min-w-0">
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-widest bg-sage-50 text-sage-600 border border-sage-200 px-2.5 py-0.5 rounded-full">
                        Khách Hàng Thân Thiết
                    </span>
                    <span class="text-xs font-mono text-stone-500">@<span>{{ $user->username }}</span></span>
                    @if($user->email_verified_at)
                        <span class="text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                            ✓ Email đã xác nhận
                        </span>
                    @else
                        <span class="text-[11px] font-bold bg-terracotta-50 text-terracotta-700 border border-terracotta-200 px-2.5 py-0.5 rounded-full">
                            ⏳ Email chưa xác nhận
                        </span>
                    @endif
                </div>
                <h1 class="font-serif text-xl sm:text-3xl font-bold text-stone-800 break-words">{{ $user->name }}</h1>
                <p class="text-xs text-stone-500 break-words leading-relaxed">
                    📧 {{ $user->email }} • 📞 {{ $user->phone ?? 'Chưa cập nhật' }} • 📍 Mặc định: {{ $user->address ?? 'TP. Hồ Chí Minh' }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('customer.wishlist') }}" class="text-xs font-semibold bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-full transition">
                ❤️ Danh Mục Yêu Thích (<span data-wishlist-count>{{ $wishlistProducts->count() }}</span>)
            </a>
            <a href="{{ route('products.index') }}" class="text-xs font-semibold bg-sage-50 hover:bg-sage-100 text-sage-700 border border-sage-200 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-full transition">
                🛍️ Tiếp tục mua sắm
            </a>
            <form action="{{ route('customer.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs font-semibold bg-stone-900 hover:bg-red-600 text-white px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-full transition cursor-pointer">
                    Đăng xuất tài khoản
                </button>
            </form>
        </div>
    </div>

    @php
        $currentTab = $activeSection ?? 'overview';
    @endphp

    <!-- Thanh Danh Mục Trong Phần Tài Khoản Khách Hàng (Cuộn ngang mượt mà trên Mobile, xuống dòng tự nhiên trên Tablet/Desktop) -->
    <nav class="bg-white rounded-2xl border border-sage-100 p-2 shadow-2xs flex flex-nowrap md:flex-wrap items-center gap-2 text-xs font-bold overflow-x-auto no-scrollbar whitespace-nowrap">
        <a href="{{ route('customer.account') }}"
            class="px-4 py-2.5 rounded-xl transition {{ $currentTab === 'overview' ? 'bg-sage-500 text-white shadow-xs' : 'text-stone-600 hover:bg-sage-50 hover:text-sage-700' }}">
            👤 Tổng Quan Tài Khoản
        </a>
        <a href="{{ route('customer.profile') }}"
            class="px-4 py-2.5 rounded-xl transition {{ $currentTab === 'profile' ? 'bg-sage-500 text-white shadow-xs' : 'text-stone-600 hover:bg-sage-50 hover:text-sage-700' }}">
            ✏️ Đổi Thông Tin & Email
        </a>
        <a href="{{ route('customer.addresses') }}"
            class="px-4 py-2.5 rounded-xl transition {{ $currentTab === 'addresses' ? 'bg-sage-500 text-white shadow-xs' : 'text-stone-600 hover:bg-sage-50 hover:text-sage-700' }}">
            📍 Sổ Địa Chỉ ({{ $addresses->count() }})
        </a>
        <a href="{{ route('customer.wishlist') }}"
            class="px-4 py-2.5 rounded-xl bg-red-50 hover:bg-red-500 text-red-600 hover:text-white border border-red-200 flex items-center gap-1.5 transition">
            <span>❤️ Yêu Thích</span>
            <span data-wishlist-count class="bg-red-500 text-white px-2 py-0.5 rounded-full text-[11px]">{{ $wishlistProducts->count() }}</span>
        </a>
        <a href="{{ route('customer.cart') }}"
            class="px-4 py-2.5 rounded-xl transition {{ $currentTab === 'cart' ? 'bg-sage-500 text-white shadow-xs' : 'text-stone-600 hover:bg-sage-50 hover:text-sage-700' }}">
            🛒 Giỏ Hàng ({{ collect($savedCart)->sum('quantity') }})
        </a>
        <a href="{{ route('customer.orders') }}"
            class="px-4 py-2.5 rounded-xl transition {{ $currentTab === 'orders' ? 'bg-sage-500 text-white shadow-xs' : 'text-stone-600 hover:bg-sage-50 hover:text-sage-700' }}">
            📦 Đơn Hàng ({{ $allMyOrders->count() }})
        </a>
        <a href="{{ route('customer.vouchers') }}"
            class="px-4 py-2.5 rounded-xl transition {{ $currentTab === 'vouchers' ? 'bg-terracotta-600 text-white shadow-xs' : 'text-stone-600 hover:bg-terracotta-50 hover:text-terracotta-700' }}">
            🎟️ Kho Voucher ({{ $activeCoupons->count() }})
        </a>
    </nav>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-2xl text-sm font-medium">
            ⚠ {{ $errors->first() }}
        </div>
    @endif

    @if($currentTab === 'overview')
    <!-- Quick Summary Cards (Chỉ hiển thị ở trang Tổng Quan Tài Khoản) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('customer.profile') }}" class="bg-white p-5 rounded-3xl border border-sage-100 shadow-xs hover:border-sage-300 transition space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400">Hồ Sơ & Xác Thực Email</span>
            <p class="text-xl font-bold text-stone-800 truncate">{{ $user->name }}</p>
            <p class="text-xs {{ $user->email_verified_at ? 'text-emerald-600' : 'text-terracotta-600' }} font-medium">
                {{ $user->email_verified_at ? '✓ Email đã xác thực OTP' : '⏳ Bấm để xác thực Email qua Gmail →' }}
            </p>
        </a>

        <a href="{{ route('customer.addresses') }}" class="bg-white p-5 rounded-3xl border border-sage-100 shadow-xs hover:border-sage-300 transition space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400">Sổ Địa Chỉ Nhận Hàng</span>
            <p class="text-2xl font-bold text-stone-800">{{ $addresses->count() }} địa chỉ</p>
            <p class="text-xs text-sage-600 font-medium">Mở trang quản lý & chọn địa chỉ mặc định →</p>
        </a>

        <a href="{{ route('customer.wishlist') }}" class="bg-white p-5 rounded-3xl border border-red-100 shadow-xs hover:border-red-300 transition space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-red-400">Danh Mục Mới: Yêu Thích</span>
            <p class="text-2xl font-bold text-red-500">❤️ <span data-wishlist-count>{{ $wishlistProducts->count() }}</span> sản phẩm</p>
            <p class="text-xs text-stone-500">Xem trang sản phẩm đã thả tim →</p>
        </a>

        <a href="{{ route('customer.cart') }}" class="bg-white p-5 rounded-3xl border border-sage-100 shadow-xs hover:border-sage-300 transition space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400">Giỏ Hàng Đã Lưu</span>
            <p class="text-2xl font-bold text-sage-600">{{ collect($savedCart)->sum('quantity') }} sản phẩm</p>
            <p class="text-xs text-stone-500">Tạm tính: {{ number_format($savedCartTotal, 0, ',', '.') }}₫</p>
        </a>

        <a href="{{ route('customer.orders') }}" class="bg-white p-5 rounded-3xl border border-sage-100 shadow-xs hover:border-sage-300 transition space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400">Đơn Hàng Đã Đặt</span>
            <p class="text-2xl font-bold text-stone-800">{{ $allMyOrders->count() }} đơn hàng</p>
            <p class="text-xs text-emerald-600 font-medium">
                Đang giao: {{ $allMyOrders->where('status', 'SHIPPING')->count() }} • Hoàn thành: {{ $allMyOrders->where('status', 'COMPLETED')->count() }}
            </p>
        </a>

        <a href="{{ route('customer.vouchers') }}" class="bg-white p-5 rounded-3xl border border-sage-100 shadow-xs hover:border-sage-300 transition space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-400">Kho Voucher Của Shop</span>
            <p class="text-2xl font-bold text-terracotta-600">{{ $activeCoupons->count() }} mã ưu đãi</p>
            <p class="text-xs text-stone-500">Giảm tối đa đến {{ $activeCoupons->max('discount_percent') ?? 30 }}% →</p>
        </a>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- TRANG 1: ĐỔI THÔNG TIN KHÁCH HÀNG & XÁC THỰC EMAIL (/tai-khoan/thong-tin) -->
    <!-- ========================================================================= -->
    @if($currentTab === 'profile')
    <div>
        <section id="section-profile" class="bg-white rounded-3xl border border-sage-100 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="border-b border-sage-100 pb-4">
                <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Hồ Sơ Cá Nhân</span>
                <h2 class="font-serif text-2xl font-bold text-stone-800 mt-0.5">
                    ✏️ Thay Đổi Thông Tin Khách Hàng
                </h2>
                <p class="text-xs text-stone-500 mt-1">
                    Cập nhật Họ và tên, Số điện thoại liên hệ và địa chỉ Email (kèm xác nhận Email).
                </p>
            </div>

            <form action="{{ route('customer.profile.update') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1.5">
                        Họ và tên khách hàng *
                    </label>
                    <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                        placeholder="Nhập họ và tên của bạn"
                        class="w-full px-4 py-2.5 text-sm rounded-2xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1.5">
                        Số điện thoại liên hệ *
                    </label>
                    <input type="tel" name="phone" required value="{{ old('phone', $user->phone) }}"
                        placeholder="VD: 0909397979"
                        class="w-full px-4 py-2.5 text-sm rounded-2xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1.5">
                        Địa chỉ Email *
                    </label>
                    <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                        placeholder="Nhập địa chỉ Email"
                        class="w-full px-4 py-2.5 text-sm rounded-2xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-600 mb-1.5">
                        Xác nhận lại Email *
                    </label>
                    <input type="email" name="email_confirmation" required value="{{ old('email_confirmation', $user->email) }}"
                        placeholder="Nhập lại địa chỉ Email để xác nhận"
                        class="w-full px-4 py-2.5 text-sm rounded-2xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                </div>

                <button type="submit" class="w-full bg-sage-500 hover:bg-sage-600 text-white font-semibold py-3 rounded-2xl shadow-md shadow-sage-200 transition text-sm cursor-pointer">
                    💾 Lưu Thay Đổi Thông Tin Cá Nhân
                </button>
            </form>

            <!-- Khối Xác thực / Xác nhận địa chỉ Email bằng mã OTP gửi về hộp thư Gmail -->
            <div class="pt-4 border-t border-sage-100 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-stone-700">📧 Trạng thái xác thực Email:</span>
                    @if($user->email_verified_at)
                        <span class="text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                            ✓ Đã xác nhận ({{ $user->email_verified_at->format('d/m/Y H:i') }})
                        </span>
                    @else
                        <span class="text-[11px] font-bold bg-terracotta-50 text-terracotta-700 border border-terracotta-200 px-2.5 py-0.5 rounded-full">
                            ⏳ Chưa xác thực OTP qua Gmail
                        </span>
                    @endif
                </div>

                @php
                    $emailOtpSession = session('email_verification_otp');
                    $hasOtpForCurrentUser = $emailOtpSession && (int)($emailOtpSession['user_id'] ?? 0) === (int)$user->id;
                    $otpMailSent = $hasOtpForCurrentUser && !empty($emailOtpSession['mail_sent']);
                    $otpMailError = $hasOtpForCurrentUser ? ($emailOtpSession['mail_error'] ?? null) : null;
                @endphp

                <div class="p-3.5 rounded-2xl bg-[#F5FAF7] border border-sage-200 space-y-3 text-xs">
                    @if($otpMailSent)
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-2.5 leading-relaxed">
                            <p>
                                📬 Đã gửi thư chứa mã xác thực 6 chữ số về hộp thư Gmail <strong>{{ $emailOtpSession['email'] }}</strong>.
                                Vui lòng mở hộp thư đến Gmail của bạn để lấy mã 6 số và nhập vào ô bên dưới:
                            </p>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('customer.email.inbox') }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl shadow-xs transition">
                                    📩 Mở Hộp Thư Đến Gmail ({{ $emailOtpSession['email'] }}) ↗
                                </a>
                                <a href="https://mail.google.com/mail/u/0/#inbox" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1 bg-white hover:bg-emerald-100/60 text-emerald-800 border border-emerald-300 font-semibold text-xs px-3 py-2 rounded-xl transition">
                                    🌐 Mở mail.google.com ↗
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-stone-600 leading-relaxed">
                                Mã xác thực 6 chữ số sẽ được gửi trực tiếp về hộp thư Gmail <strong>{{ $user->email }}</strong> của bạn.
                            </p>
                            <a href="{{ route('customer.email.inbox') }}" target="_blank"
                               class="text-[11px] font-bold text-sage-600 hover:underline">
                                📩 Mở Hộp Thư Đến Gmail ({{ $user->email }}) ↗
                            </a>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-2">
                        <form action="{{ route('customer.email.send-otp') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-stone-900 hover:bg-sage-600 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition cursor-pointer">
                                📨 {{ $otpMailSent ? 'Gửi lại mã về Gmail' : 'Gửi mã xác nhận về Gmail' }}
                            </button>
                        </form>

                        <form action="{{ route('customer.email.verify') }}" method="POST" class="flex-1 flex items-center gap-1.5 min-w-[200px]">
                            @csrf
                            <input type="text" name="email_otp" maxlength="6" required
                                value=""
                                autocomplete="one-time-code"
                                inputmode="numeric"
                                placeholder="Nhập mã 6 số trong Gmail"
                                class="w-full px-3 py-2 text-xs font-mono tracking-widest font-bold rounded-xl border border-sage-200 bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition flex-shrink-0 cursor-pointer">
                                ✓ Xác nhận
                            </button>
                        </form>
                    </div>

                    <!-- Khối cấu hình tùy chọn Gmail SMTP (ẩn mặc định để không gây vướng) -->
                    <div class="pt-2 border-t border-sage-200/70">
                        <button type="button"
                            onclick="document.getElementById('gmail-smtp-config-box').classList.toggle('hidden')"
                            class="text-[11px] font-semibold text-stone-500 hover:text-terracotta-600 flex items-center gap-1 cursor-pointer">
                            ⚙ {{ !empty($gmailSmtpConfigured) ? 'Đã kết nối SMTP (' . $gmailSmtpSender . ')' : 'Tùy chọn: Cấu hình SMTP Google App Password riêng' }}
                        </button>

                        <form id="gmail-smtp-config-box"
                            action="{{ route('customer.email.configure-smtp') }}"
                            method="POST"
                            class="{{ $otpMailError ? '' : 'hidden' }} mt-2.5 p-3 rounded-xl bg-white border border-terracotta-200 space-y-2.5">
                            @csrf
                            <p class="text-[11px] text-stone-600 leading-relaxed">
                                Để hệ thống gửi thư thật qua máy chủ <code>smtp.gmail.com</code> về hộp thư Gmail, hãy nhập <strong>Email Gmail gửi thư</strong> và <strong>Mật khẩu ứng dụng Google 16 ký tự (App Password)</strong> (tạo tại <em>Google Account → Bảo mật → Xác minh 2 bước → Mật khẩu ứng dụng</em>):
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-stone-500 mb-1">Gmail gửi thư (MAIL_USERNAME)</label>
                                    <input type="email" name="smtp_email" required
                                        value="{{ old('smtp_email', !empty($gmailSmtpSender) ? $gmailSmtpSender : $user->email) }}"
                                        placeholder="vd: tenban@gmail.com"
                                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-stone-500 mb-1">Google App Password (16 ký tự)</label>
                                    <input type="password" name="smtp_app_password" required
                                        placeholder="xxxx xxxx xxxx xxxx"
                                        class="w-full px-3 py-1.5 text-xs font-mono rounded-lg border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none">
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-terracotta-600 hover:bg-terracotta-700 text-white text-xs font-semibold py-2 rounded-lg transition cursor-pointer">
                                💾 Lưu Cấu Hình Gmail SMTP & Gửi Ngay Mã OTP Về {{ $user->email }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- TRANG 2: SỔ ĐỊA CHỈ NHẬN HÀNG (/tai-khoan/dia-chi) -->
    <!-- ========================================================================= -->
    @if($currentTab === 'addresses')
    <div>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

        <section id="section-addresses" class="bg-white rounded-3xl border border-sage-100 p-6 sm:p-8 shadow-xs space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-sage-100 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Giao Nhận Nhanh Chóng</span>
                    <h2 class="font-serif text-2xl font-bold text-stone-800 mt-0.5">
                        📍 Sổ Địa Chỉ Nhận Hàng ({{ $addresses->count() }} địa chỉ)
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        Lưu sẵn 2 – 3+ địa chỉ (Nhà riêng, Văn phòng, Người thân...), chọn vị trí nhanh trên bản đồ và bấm <strong>"Đặt làm mặc định"</strong> để thanh toán ngay không cần nhập lại.
                    </p>
                </div>
                <button type="button" onclick="openCustomerAddressMapForAdd()"
                    class="text-xs font-bold bg-sage-500 hover:bg-sage-600 text-white px-4 py-2.5 rounded-full shadow-xs transition cursor-pointer">
                    + Thêm Địa Chỉ Mới
                </button>
            </div>

            <!-- Form thêm địa chỉ mới -->
            <div id="add-address-form-box" class="hidden p-5 rounded-2xl bg-[#F5FAF7] border border-sage-200 space-y-4 text-xs">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-sm text-stone-800">➕ Thêm Địa Chỉ Nhận Hàng Mới</h3>
                    <button type="button" onclick="document.getElementById('add-address-form-box').classList.add('hidden')" class="text-stone-400 hover:text-stone-700 font-bold cursor-pointer">✕ Đóng</button>
                </div>

                <form id="add-address-form" action="{{ route('customer.addresses.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Nhãn địa chỉ *</label>
                            <input type="text" name="label" required value="Địa chỉ phụ" placeholder="VD: Nhà riêng, Công ty..."
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-sage-200 bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Người nhận</label>
                            <input type="text" name="recipient_name" value="{{ $user->name }}" placeholder="Họ tên người nhận"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-sage-200 bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">SĐT nhận hàng</label>
                            <input type="text" name="recipient_phone" value="{{ $user->phone }}" placeholder="Số điện thoại"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-sage-200 bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Tỉnh / Thành phố *</label>
                            <input type="text" name="province" required value="TP. Hồ Chí Minh" placeholder="VD: TP. Hồ Chí Minh"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-sage-200 bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Quận / Huyện *</label>
                            <input type="text" name="district" required placeholder="VD: Quận 3"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-sage-200 bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Phường / Xã *</label>
                            <input type="text" name="ward" required placeholder="VD: Phường Võ Thị Sáu"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-sage-200 bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-stone-600 mb-1">Số nhà, tên đường, tòa nhà (Tự nhập hoặc chọn trên bản đồ bên dưới) *</label>
                        <input type="text" name="street_address" required placeholder="VD: Số 120 Nguyễn Đình Chiểu (hoặc chạm vào bản đồ bên dưới)"
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-sage-200 bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                    </div>

                    <!-- Vị trí gắn bản đồ khi Thêm địa chỉ mới (nằm dưới phần nhập địa chỉ bằng tay) -->
                    <div id="add-address-map-slot" class="pt-1">
                        <!-- BẢN ĐỒ CHỌN NHANH VỊ TRÍ GIAO HÀNG (DÙNG CHUNG CHO THÊM MỚI & THAY ĐỔI ĐỊA CHỈ) -->
                        <div id="customer-address-map-widget" class="bg-white rounded-2xl border border-sage-200 p-3.5 space-y-3 shadow-2xs">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <span class="text-xs font-bold text-stone-800 flex items-center gap-1.5">
                                        🗺️ Chọn Nhanh Vị Trí Trên Bản Đồ
                                    </span>
                                    <p class="text-[11px] text-stone-500">
                                        Chạm vào bản đồ hoặc kéo ghim đỏ để hệ thống <strong>tự động điền Tỉnh/TP, Quận/Huyện, Phường/Xã &amp; Số nhà tên đường</strong>.
                                    </p>
                                </div>
                                <button type="button" id="btn-locate-me" onclick="locateMyCurrentPosition()"
                                    class="inline-flex items-center gap-1.5 bg-sage-500 hover:bg-sage-600 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition shadow-xs cursor-pointer">
                                    📍 Lấy vị trí hiện tại (GPS)
                                </button>
                            </div>

                            <!-- Thanh tìm kiếm nhanh địa điểm trên bản đồ -->
                            <div class="flex gap-2">
                                <input type="text" id="map-search-input"
                                    placeholder="🔍 Tìm nhanh đường phố, tòa nhà, địa điểm trên bản đồ (VD: 28 Phạm Văn Chiêu, Gò Vấp)..."
                                    onkeydown="if(event.key==='Enter'){event.preventDefault();searchLocationOnMap();}"
                                    class="flex-1 px-3.5 py-2 text-xs rounded-xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-300">
                                <button type="button" onclick="searchLocationOnMap()"
                                    class="bg-stone-900 hover:bg-sage-600 text-white text-xs font-semibold px-4 py-2 rounded-xl transition cursor-pointer flex-shrink-0">
                                    Tìm trên bản đồ
                                </button>
                            </div>

                            <!-- Nút nhảy nhanh chọn khu vực mẫu trên bản đồ -->
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                <span class="text-stone-500 font-semibold">Chọn nhanh khu vực:</span>
                                <button type="button" onclick="quickSelectTestLocation('TP. Hồ Chí Minh', 'Quận 1', 'Phường Bến Thành', 10.7769, 106.7009)" class="px-2.5 py-1 rounded-full bg-sage-50 hover:bg-sage-100 text-sage-700 border border-sage-200 font-medium cursor-pointer">
                                    🏙️ Nội thành TP.HCM
                                </button>
                                <button type="button" onclick="quickSelectTestLocation('TP. Hồ Chí Minh', 'Huyện Củ Chi', 'Thị trấn Củ Chi', 10.9733, 106.4933)" class="px-2.5 py-1 rounded-full bg-sage-50 hover:bg-sage-100 text-sage-700 border border-sage-200 font-medium cursor-pointer">
                                    🏘️ Huyện Củ Chi
                                </button>
                                <button type="button" onclick="quickSelectTestLocation('Thành phố Đà Nẵng', 'Quận Hải Châu', 'Phường Hải Châu I', 16.0544, 108.2022)" class="px-2.5 py-1 rounded-full bg-sage-50 hover:bg-sage-100 text-sage-700 border border-sage-200 font-medium cursor-pointer">
                                    🚛 TP. Đà Nẵng
                                </button>
                                <button type="button" onclick="quickSelectTestLocation('Tỉnh Lào Cai', 'Thị xã Sa Pa', 'Phường Sa Pa', 22.3364, 103.8438)" class="px-2.5 py-1 rounded-full bg-sage-50 hover:bg-sage-100 text-sage-700 border border-sage-200 font-medium cursor-pointer">
                                    ⛰️ Sa Pa - Lào Cai
                                </button>
                            </div>

                            <!-- Khung hiển thị bản đồ tương tác -->
                            <div class="relative rounded-2xl overflow-hidden border border-sage-200">
                                <div id="shipping-map" class="w-full h-64 sm:h-72 z-10 bg-sage-50"></div>
                                <div id="map-status-badge" class="hidden text-[11px] font-medium px-3 py-1.5 bg-emerald-50 text-emerald-800 border-t border-emerald-200 flex items-center justify-between">
                                    <span id="map-status-text">✓ Đã chọn vị trí trên bản đồ</span>
                                    <span id="map-coords-text" class="font-mono text-[10px] text-emerald-600"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-stone-700 font-medium">
                            <input type="checkbox" name="is_default" value="1" checked class="rounded accent-sage-600">
                            <span>Đặt làm địa chỉ nhận hàng mặc định ngay</span>
                        </label>
                        <button type="submit" class="bg-sage-500 hover:bg-sage-600 text-white font-semibold px-5 py-2.5 rounded-xl shadow-xs transition cursor-pointer">
                            ✓ Lưu Địa Chỉ Mới
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danh sách các địa chỉ đã lưu -->
            <div class="space-y-3.5">
                @foreach($addresses as $addr)
                    <div class="p-4 rounded-2xl border-2 transition {{ $addr->is_default ? 'border-sage-500 bg-sage-50/50 shadow-xs' : 'border-sage-100 bg-[#F5FAF7] hover:border-sage-200' }} space-y-3">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-bold text-stone-800 bg-white border border-sage-200 px-2.5 py-0.5 rounded-lg">
                                        🏷️ {{ $addr->label }}
                                    </span>
                                    @if($addr->is_default)
                                        <span class="text-[11px] font-bold bg-sage-600 text-white px-2.5 py-0.5 rounded-full shadow-2xs">
                                            ★ Địa chỉ Mặc Định
                                        </span>
                                    @endif
                                    <span class="text-xs font-semibold text-stone-700">
                                        {{ $addr->recipient_name ?: $user->name }} • 📞 {{ $addr->recipient_phone ?: $user->phone }}
                                    </span>
                                </div>
                                <p class="text-xs text-stone-700 font-medium leading-relaxed pt-0.5">
                                    📍 {{ $addr->address }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                                @if(!$addr->is_default)
                                    <form action="{{ route('customer.addresses.default', $addr) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-bold bg-terracotta-600 hover:bg-terracotta-700 text-white px-3 py-1.5 rounded-xl transition shadow-2xs cursor-pointer">
                                            ★ Đặt làm mặc định
                                        </button>
                                    </form>
                                @endif

                                <button type="button" onclick="openCustomerAddressMapForEdit({{ $addr->id }})"
                                    class="text-xs font-semibold text-sage-700 bg-white hover:bg-sage-50 border border-sage-200 px-3 py-1.5 rounded-xl transition cursor-pointer">
                                    Sửa / Chọn trên bản đồ
                                </button>

                                @if($addresses->count() > 1)
                                    <form action="{{ route('customer.addresses.destroy', $addr) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-500 bg-white hover:bg-red-50 border border-red-100 px-2.5 py-1.5 rounded-xl transition cursor-pointer">
                                            Xóa
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Form chỉnh sửa nhanh địa chỉ này -->
                        <div id="edit-addr-{{ $addr->id }}" class="hidden pt-3 border-t border-sage-200/70">
                            <form id="edit-address-form-{{ $addr->id }}" action="{{ route('customer.addresses.update', $addr) }}" method="POST" class="space-y-3 text-xs">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-stone-500 mb-1">Nhãn địa chỉ *</label>
                                        <input type="text" name="label" required value="{{ $addr->label }}"
                                            class="w-full px-3 py-2 text-xs rounded-xl border border-sage-200 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-stone-500 mb-1">Người nhận</label>
                                        <input type="text" name="recipient_name" value="{{ $addr->recipient_name ?: $user->name }}"
                                            class="w-full px-3 py-2 text-xs rounded-xl border border-sage-200 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-stone-500 mb-1">Số điện thoại</label>
                                        <input type="text" name="recipient_phone" value="{{ $addr->recipient_phone ?: $user->phone }}"
                                            class="w-full px-3 py-2 text-xs rounded-xl border border-sage-200 bg-white">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-stone-500 mb-1">Tỉnh / Thành phố *</label>
                                        <input type="text" name="province" required value="{{ $addr->province ?: 'TP. Hồ Chí Minh' }}"
                                            class="w-full px-3 py-2 text-xs rounded-xl border border-sage-200 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-stone-500 mb-1">Quận / Huyện *</label>
                                        <input type="text" name="district" required value="{{ $addr->district ?: 'Quận 1' }}"
                                            class="w-full px-3 py-2 text-xs rounded-xl border border-sage-200 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-stone-500 mb-1">Phường / Xã *</label>
                                        <input type="text" name="ward" required value="{{ $addr->ward ?: 'Phường Bến Thành' }}"
                                            class="w-full px-3 py-2 text-xs rounded-xl border border-sage-200 bg-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-stone-500 mb-1">Số nhà, tên đường (Tự nhập hoặc chọn trên bản đồ bên dưới) *</label>
                                    <input type="text" name="street_address" required value="{{ $addr->street_address ?: $addr->address }}"
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-sage-200 bg-white">
                                </div>

                                <!-- Vị trí gắn bản đồ khi Sửa / Thay đổi địa chỉ này (nằm dưới phần nhập địa chỉ bằng tay) -->
                                <div id="edit-address-map-slot-{{ $addr->id }}" class="pt-1"></div>

                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-2 cursor-pointer text-stone-600">
                                        <input type="checkbox" name="is_default" value="1" {{ $addr->is_default ? 'checked' : '' }} class="rounded accent-sage-600">
                                        <span>Đặt làm địa chỉ mặc định</span>
                                    </label>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="document.getElementById('edit-addr-{{ $addr->id }}').classList.add('hidden')" class="px-3 py-1.5 rounded-xl border border-stone-200 bg-white text-stone-600 cursor-pointer">Hủy</button>
                                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-sage-500 hover:bg-sage-600 text-white font-semibold cursor-pointer">Lưu cập nhật</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mapWidget = document.getElementById('customer-address-map-widget');
                const mapContainer = document.getElementById('shipping-map');
                let shippingMap = null;
                let shippingMarker = null;
                let activeAddressForm = document.getElementById('add-address-form');

                const CITY_COORDS = {
                    'TP. Hồ Chí Minh': [10.7769, 106.7009],
                    'Thành phố Hồ Chí Minh': [10.7769, 106.7009],
                    'Thành phố Hà Nội': [21.0285, 105.8542],
                    'Thành phố Đà Nẵng': [16.0544, 108.2022],
                    'Thành phố Cần Thơ': [10.0452, 105.7469],
                    'Thành phố Hải Phòng': [20.8449, 106.6881],
                    'Tỉnh Bình Dương': [10.9804, 106.6519],
                    'Tỉnh Đồng Nai': [10.9574, 106.8427],
                    'Tỉnh Khánh Hòa': [12.2388, 109.1967],
                    'Tỉnh Lâm Đồng': [11.9404, 108.4583],
                    'Tỉnh Bà Rịa - Vũng Tàu': [10.3460, 107.0843],
                    'Tỉnh Thừa Thiên Huế': [16.4637, 107.5909],
                    'Tỉnh Nghệ An': [18.6796, 105.6813],
                    'Tỉnh Thanh Hóa': [19.8067, 105.7852],
                    'Tỉnh Quảng Ninh': [20.9599, 107.0425],
                    'Tỉnh Lào Cai': [22.3364, 103.8438]
                };

                function ensureMapInitialized() {
                    if (shippingMap || !mapContainer || typeof L === 'undefined') {
                        if (shippingMap) {
                            setTimeout(() => shippingMap.invalidateSize(), 120);
                        }
                        return;
                    }
                    const defaultCenter = [10.7769, 106.7009];
                    shippingMap = L.map('shipping-map').setView(defaultCenter, 14);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(shippingMap);

                    shippingMarker = L.marker(defaultCenter, { draggable: true }).addTo(shippingMap);
                    shippingMarker.bindPopup('<b>📍 Vị trí nhận hàng</b><br>Kéo ghim hoặc chạm vào bản đồ để tự động điền địa chỉ.').openPopup();

                    shippingMap.on('click', function (e) {
                        const { lat, lng } = e.latlng;
                        shippingMarker.setLatLng([lat, lng]);
                        reverseGeocodeAndFillAddress(lat, lng);
                    });

                    shippingMarker.on('dragend', function () {
                        const pos = shippingMarker.getLatLng();
                        reverseGeocodeAndFillAddress(pos.lat, pos.lng);
                    });

                    setTimeout(() => shippingMap.invalidateSize(), 120);
                }

                function fillActiveFormFields(province, district, ward, street) {
                    if (!activeAddressForm) return;
                    const provEl = activeAddressForm.querySelector('input[name="province"]');
                    const distEl = activeAddressForm.querySelector('input[name="district"]');
                    const wardEl = activeAddressForm.querySelector('input[name="ward"]');
                    const streetEl = activeAddressForm.querySelector('input[name="street_address"]');

                    if (provEl && province) provEl.value = province;
                    if (distEl && district) distEl.value = district;
                    if (wardEl && ward) wardEl.value = ward;
                    if (streetEl && street) streetEl.value = street;
                }

                function panMapToProvinceName(provinceName) {
                    if (!shippingMap || !shippingMarker || !provinceName) return;
                    for (const [key, coords] of Object.entries(CITY_COORDS)) {
                        if (provinceName.toLowerCase().includes(key.toLowerCase()) || key.toLowerCase().includes(provinceName.toLowerCase())) {
                            shippingMap.flyTo(coords, 13, { duration: 0.6 });
                            shippingMarker.setLatLng(coords);
                            return;
                        }
                    }
                }

                window.openCustomerAddressMapForAdd = function () {
                    const addBox = document.getElementById('add-address-form-box');
                    const addSlot = document.getElementById('add-address-map-slot');
                    if (!addBox) return;

                    const willOpen = addBox.classList.contains('hidden');
                    document.querySelectorAll('[id^="edit-addr-"]').forEach(el => el.classList.add('hidden'));

                    if (willOpen) {
                        addBox.classList.remove('hidden');
                        activeAddressForm = document.getElementById('add-address-form');
                        if (addSlot && mapWidget) {
                            addSlot.appendChild(mapWidget);
                        }
                        ensureMapInitialized();
                    } else {
                        addBox.classList.add('hidden');
                    }
                };

                window.openCustomerAddressMapForEdit = function (addrId) {
                    const editBox = document.getElementById('edit-addr-' + addrId);
                    const editSlot = document.getElementById('edit-address-map-slot-' + addrId);
                    const addBox = document.getElementById('add-address-form-box');
                    if (!editBox) return;

                    const willOpen = editBox.classList.contains('hidden');
                    if (addBox) addBox.classList.add('hidden');
                    document.querySelectorAll('[id^="edit-addr-"]').forEach(el => el.classList.add('hidden'));

                    if (willOpen) {
                        editBox.classList.remove('hidden');
                        activeAddressForm = document.getElementById('edit-address-form-' + addrId);
                        if (editSlot && mapWidget) {
                            editSlot.appendChild(mapWidget);
                        }
                        ensureMapInitialized();
                        const provEl = activeAddressForm ? activeAddressForm.querySelector('input[name="province"]') : null;
                        if (provEl && provEl.value) {
                            setTimeout(() => panMapToProvinceName(provEl.value), 160);
                        }
                    }
                };

                async function reverseGeocodeAndFillAddress(lat, lng) {
                    const statusBadge = document.getElementById('map-status-badge');
                    const statusText = document.getElementById('map-status-text');
                    const coordsText = document.getElementById('map-coords-text');

                    if (statusBadge) statusBadge.classList.remove('hidden');
                    if (statusText) statusText.textContent = '⏳ Đang xác định địa chỉ từ vị trí bản đồ...';
                    if (coordsText) coordsText.textContent = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;

                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=vi&addressdetails=1`);
                        if (!res.ok) throw new Error('Reverse geocode error');
                        const data = await res.json();
                        const addr = data.address || {};

                        const rawProvince = addr.city || addr.state || addr.province || 'TP. Hồ Chí Minh';
                        const rawDistrict = addr.city_district || addr.County || addr.county || addr.district || addr.suburb || addr.town || 'Quận 1';
                        const rawWard = addr.quarter || addr.suburb || addr.village || addr.neighbourhood || addr.hamlet || 'Phường Bến Thành';
                        const houseNum = addr.house_number || '';
                        const road = addr.road || addr.pedestrian || addr.Amenity || addr.amenity || addr.building || '';
                        const streetLine = [houseNum, road].filter(Boolean).join(' ').trim() || (data.name || `Vị trí bản đồ (${lat.toFixed(4)}, ${lng.toFixed(4)})`);

                        fillActiveFormFields(rawProvince, rawDistrict, rawWard, streetLine);

                        if (statusText) {
                            statusText.textContent = '✓ Đã tự động điền Tỉnh/TP, Quận/Huyện, Phường/Xã & Số nhà tên đường từ bản đồ!';
                        }
                    } catch (e) {
                        const streetEl = activeAddressForm ? activeAddressForm.querySelector('input[name="street_address"]') : null;
                        if (streetEl && !streetEl.value.trim()) {
                            streetEl.value = `Tọa độ bản đồ (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
                        }
                        if (statusText) {
                            statusText.textContent = '✓ Đã ghim tọa độ vị trí giao hàng trên bản đồ';
                        }
                    }
                }

                window.quickSelectTestLocation = function (provName, distName, wardName, lat, lng) {
                    const streetEl = activeAddressForm ? activeAddressForm.querySelector('input[name="street_address"]') : null;
                    const defaultStreet = (streetEl && streetEl.value.trim()) ? streetEl.value.trim() : 'Số 12 Đường Trung Tâm';
                    fillActiveFormFields(provName, distName, wardName, defaultStreet);

                    if (shippingMap && shippingMarker && lat && lng) {
                        shippingMap.flyTo([lat, lng], 14, { duration: 0.8 });
                        shippingMarker.setLatLng([lat, lng]);
                    }
                    const statusBadge = document.getElementById('map-status-badge');
                    const statusText = document.getElementById('map-status-text');
                    const coordsText = document.getElementById('map-coords-text');
                    if (statusBadge) statusBadge.classList.remove('hidden');
                    if (statusText) statusText.textContent = `✓ Đã chọn nhanh khu vực ${wardName}, ${distName}, ${provName}`;
                    if (coordsText && lat && lng) coordsText.textContent = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                };

                window.locateMyCurrentPosition = function () {
                    const btn = document.getElementById('btn-locate-me');
                    if (!navigator.geolocation) {
                        alert('Trình duyệt của bạn không hỗ trợ định vị GPS.');
                        return;
                    }
                    if (btn) btn.textContent = '⏳ Đang định vị GPS...';
                    navigator.geolocation.getCurrentPosition(
                        function (pos) {
                            if (btn) btn.textContent = '📍 Lấy vị trí hiện tại (GPS)';
                            const lat = pos.coords.latitude;
                            const lng = pos.coords.longitude;
                            ensureMapInitialized();
                            if (shippingMap && shippingMarker) {
                                shippingMap.flyTo([lat, lng], 16, { duration: 0.8 });
                                shippingMarker.setLatLng([lat, lng]);
                            }
                            reverseGeocodeAndFillAddress(lat, lng);
                        },
                        function () {
                            if (btn) btn.textContent = '📍 Lấy vị trí hiện tại (GPS)';
                            alert('Không thể truy cập GPS. Bạn có thể chạm trực tiếp vào bản đồ hoặc dùng ô tìm kiếm trên bản đồ.');
                        },
                        { enableHighAccuracy: true, timeout: 8000 }
                    );
                };

                window.searchLocationOnMap = async function () {
                    const searchInput = document.getElementById('map-search-input');
                    const query = searchInput ? searchInput.value.trim() : '';
                    if (!query) return;

                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(query)}&countrycodes=vn&limit=1&accept-language=vi`);
                        const results = await res.json();
                        if (Array.isArray(results) && results.length > 0) {
                            const lat = parseFloat(results[0].lat);
                            const lng = parseFloat(results[0].lon);
                            ensureMapInitialized();
                            if (shippingMap && shippingMarker) {
                                shippingMap.flyTo([lat, lng], 16, { duration: 0.8 });
                                shippingMarker.setLatLng([lat, lng]);
                            }
                            reverseGeocodeAndFillAddress(lat, lng);
                        } else {
                            alert('Không tìm thấy địa điểm này. Vui lòng thử từ khóa khác hoặc chạm trực tiếp lên bản đồ.');
                        }
                    } catch (e) {
                        alert('Không thể tìm kiếm lúc này, vui lòng chạm trực tiếp vào vị trí trên bản đồ.');
                    }
                };
            });
        </script>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- TRANG 3: GIỎ HÀNG ĐÃ LƯU (/tai-khoan/gio-hang) -->
    <!-- ========================================================================= -->
    @if($currentTab === 'cart')
    <div>
        <!-- Saved Cart Items -->
        <section id="section-saved-cart" class="bg-white rounded-3xl border border-sage-100 p-6 sm:p-8 shadow-xs space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-sage-100 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Đồng Bộ Tự Động Với Tài Khoản</span>
                    <h2 class="font-serif text-2xl font-bold text-stone-800">
                        🛒 Giỏ Hàng Đã Lưu Từ Trước ({{ count($savedCart) }} loại SP)
                    </h2>
                </div>
                @if(count($savedCart) > 0)
                    <a href="{{ route('checkout.index') }}" class="text-xs font-bold bg-sage-500 hover:bg-sage-600 text-white px-4 py-2 rounded-full shadow-xs transition">
                        Thanh Toán Ngay →
                    </a>
                @endif
            </div>

            @if(empty($savedCart))
                <div class="text-center py-12 space-y-3">
                    <p class="font-serif text-lg text-stone-700">Giỏ hàng của bạn hiện đang trống</p>
                    <p class="text-xs text-stone-400">Mọi sản phẩm bạn thêm vào giỏ khi đăng nhập sẽ được lưu trữ lâu dài tại đây.</p>
                    <a href="{{ route('products.index') }}" class="inline-block bg-sage-500 text-white text-xs font-semibold px-5 py-2.5 rounded-full">
                        Khám phá mỹ phẩm ngay
                    </a>
                </div>
            @else
                <div class="space-y-3.5">
                    @foreach($savedCart as $item)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl border border-sage-100 bg-[#F5FAF7] hover:border-sage-200 transition">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-16 h-16 rounded-2xl object-cover bg-white border border-sage-100 flex-shrink-0">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-[10px] bg-stone-900 text-white px-2 py-0.5 rounded-md">
                                            {{ $item['sku'] ?? ('LMN-SP' . str_pad((string)$item['id'], 3, '0', STR_PAD_LEFT)) }}
                                        </span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-sage-500">{{ $item['brand_name'] ?? 'Shine' }}</span>
                                        <span class="text-[10px] text-emerald-600 font-semibold">• Kho: {{ $item['stock'] ?? 20 }} SP</span>
                                    </div>
                                    <a href="{{ route('products.show', $item['slug']) }}" class="block text-sm font-bold text-stone-800 hover:text-sage-600 line-clamp-1 mt-0.5">
                                        {{ $item['name'] }}
                                    </a>
                                    <p class="text-xs text-stone-400">{{ $item['volume'] }} • {{ $item['skin_type'] }}</p>
                                    <p class="text-sm font-bold text-sage-600 mt-0.5">
                                        {{ number_format($item['price'], 0, ',', '.') }}₫
                                        <span class="text-xs font-normal text-stone-400">x {{ $item['quantity'] }} = {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}₫</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 flex-shrink-0">
                                <div class="flex items-center border border-sage-200 rounded-full bg-white px-1 py-0.5">
                                    <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                        <button type="submit" class="w-7 h-7 rounded-full hover:bg-sage-50 text-stone-600 font-bold text-xs">-</button>
                                    </form>
                                    <span class="w-7 text-center text-xs font-bold text-stone-800">{{ $item['quantity'] }}</span>
                                    <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                        <button type="submit" class="w-7 h-7 rounded-full hover:bg-sage-50 text-stone-600 font-bold text-xs">+</button>
                                    </form>
                                </div>

                                <form action="{{ route('cart.remove') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                    <button type="submit" class="text-xs font-semibold text-red-500 hover:bg-red-50 px-3 py-1.5 rounded-full border border-red-100">
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-sage-100 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <span class="text-xs text-stone-500">Tổng giá trị giỏ hàng đã lưu:</span>
                        <p class="text-xl font-bold text-sage-600">{{ number_format($savedCartTotal, 0, ',', '.') }}₫</p>
                    </div>
                    <a href="{{ route('checkout.index') }}" class="bg-sage-500 hover:bg-sage-600 text-white font-semibold px-7 py-3 rounded-full text-sm shadow-md shadow-sage-200 transition">
                        Tiến Hành Đặt Hàng Ngay →
                    </a>
                </div>
            @endif
        </section>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- TRANG 4: KHO VOUCHER ƯU ĐÃI (/tai-khoan/voucher) -->
    <!-- ========================================================================= -->
    @if($currentTab === 'vouchers')
    <div>
        <!-- Shop Vouchers for Customer Account (Terracotta Tone) -->
        <section id="section-vouchers" class="bg-gradient-to-br from-terracotta-50 via-[#FFF8F5] to-terracotta-100/60 rounded-3xl border border-terracotta-200 p-6 sm:p-8 shadow-xs space-y-5">
            <div class="border-b border-terracotta-200/80 pb-4">
                <span class="text-xs font-bold uppercase tracking-widest text-terracotta-700">Ưu Đãi Dành Cho Tài Khoản Khách Hàng</span>
                <h2 class="font-serif text-2xl font-bold text-terracotta-900">
                    🎟️ Kho Voucher Hiện Có ({{ $activeCoupons->count() }})
                </h2>
                <p class="text-xs text-terracotta-800/80 mt-1">
                    Mỗi đơn hàng chỉ được áp dụng <strong>01 mã giảm giá duy nhất</strong> (không áp dụng đồng thời 2 voucher).
                </p>
            </div>

            <div class="space-y-3">
                @forelse($activeCoupons as $coupon)
                    @php
                        $isThisCouponApplied = !empty($appliedCoupon) && $appliedCoupon['code'] === $coupon->code;
                        $hasAnotherCouponApplied = !empty($appliedCoupon) && $appliedCoupon['code'] !== $coupon->code;
                    @endphp
                    <div class="p-4 rounded-2xl border {{ $isThisCouponApplied ? 'border-terracotta-600 bg-terracotta-600 text-white shadow-md' : 'border-terracotta-200 bg-gradient-to-r from-white via-terracotta-50/70 to-terracotta-100/50' }} flex items-center justify-between gap-3 relative overflow-hidden">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-base {{ $isThisCouponApplied ? 'text-white bg-terracotta-700 border-terracotta-500' : 'text-terracotta-800 bg-terracotta-100 border-terracotta-300' }} px-2.5 py-0.5 rounded-lg border">
                                    {{ $coupon->code }}
                                </span>
                                <span class="text-xs font-bold {{ $isThisCouponApplied ? 'bg-white text-terracotta-800' : 'bg-terracotta-600 text-white' }} px-2.5 py-0.5 rounded-full">
                                    Giảm {{ $coupon->discount_percent }}%
                                </span>
                            </div>
                            <p class="text-xs {{ $isThisCouponApplied ? 'text-terracotta-50' : 'text-stone-700' }} font-medium">
                                {{ $coupon->description ?? "Giảm {$coupon->discount_percent}% cho mọi đơn hàng mỹ phẩm chính hãng" }}
                            </p>
                            <p class="text-[11px] {{ $isThisCouponApplied ? 'text-terracotta-200' : 'text-terracotta-700/75' }}">
                                Đơn tối thiểu: {{ number_format($coupon->min_order_amount ?? 0, 0, ',', '.') }}₫ • HSD: {{ $coupon->expires_at?->format('d/m/Y') }}
                            </p>
                        </div>

                        @if($isThisCouponApplied)
                            <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                                <span class="bg-white text-terracotta-800 text-xs font-bold px-3 py-1.5 rounded-xl shadow-xs">
                                    ✓ Đang dùng
                                </span>
                                <form action="{{ route('checkout.coupon.remove') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-[11px] text-terracotta-100 font-bold hover:underline">
                                        Gỡ mã này
                                    </button>
                                </form>
                            </div>
                        @elseif($hasAnotherCouponApplied)
                            <button type="button" disabled title="Mỗi đơn chỉ được áp dụng 1 voucher. Vui lòng gỡ mã {{ $appliedCoupon['code'] }} trước."
                                class="flex-shrink-0 bg-terracotta-100/80 text-terracotta-400 border border-terracotta-200 text-xs font-semibold px-3.5 py-2 rounded-xl cursor-not-allowed">
                                Đã dùng {{ $appliedCoupon['code'] }}
                            </button>
                        @else
                            <form action="{{ route('customer.voucher.apply') }}" method="POST" class="flex-shrink-0">
                                @csrf
                                <input type="hidden" name="coupon_code" value="{{ $coupon->code }}">
                                <button type="submit" class="bg-terracotta-600 hover:bg-terracotta-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition shadow-xs">
                                    Áp dụng ngay
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-terracotta-700/70 text-center py-8">Hiện shop chưa có mã giảm giá nào đang mở.</p>
                @endforelse
            </div>
        </section>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- TRANG 5: ĐƠN HÀNG CỦA TÔI (/tai-khoan/don-hang) -->
    <!-- ========================================================================= -->
    @if($currentTab === 'orders')
    <section id="section-my-orders" class="bg-white rounded-3xl border border-sage-100 p-6 sm:p-8 shadow-xs space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-sage-100 pb-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Lịch Sử Mua Sắm & Vận Chuyển</span>
                <h2 class="font-serif text-2xl font-bold text-stone-800">
                    📦 Đơn Hàng Của Tôi ({{ $myOrders->count() }})
                </h2>
            </div>

            <!-- Filter by Status -->
            <div class="flex flex-wrap gap-1.5">
                @foreach([
                    'ALL' => 'Tất cả (' . $allMyOrders->count() . ')',
                    'PENDING' => 'Chờ xác nhận (' . $allMyOrders->where('status', 'PENDING')->count() . ')',
                    'CONFIRMED' => 'Đã xác nhận (' . $allMyOrders->where('status', 'CONFIRMED')->count() . ')',
                    'SHIPPING' => 'Đang giao (' . $allMyOrders->where('status', 'SHIPPING')->count() . ')',
                    'COMPLETED' => 'Hoàn thành (' . $allMyOrders->where('status', 'COMPLETED')->count() . ')',
                    'CANCELLED' => 'Đã hủy (' . $allMyOrders->where('status', 'CANCELLED')->count() . ')',
                ] as $stCode => $stLabel)
                    <a href="{{ route('customer.orders', ['status' => $stCode]) }}"
                        class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition {{ $statusFilter === $stCode ? 'bg-sage-500 text-white shadow-xs' : 'bg-sage-50/70 text-stone-600 hover:bg-sage-100' }}">
                        {{ $stLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        @if($myOrders->isEmpty())
            <div class="text-center py-12 space-y-2">
                <p class="font-serif text-lg text-stone-700">Chưa có đơn hàng nào ở trạng thái này</p>
                <p class="text-xs text-stone-400">Hãy đặt mua từ giỏ hàng đã lưu của bạn để theo dõi tiến trình giao hàng.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($myOrders as $order)
                    @php
                        $isQrUnpaid = $order->isQrPayment() && !$order->isPaid();
                        $isQrPaid = $order->isQrPayment() && $order->isPaid();
                        $statusBadge = [
                            'PENDING' => ['label' => '⏳ Chờ Shop Xác Nhận', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                            'CONFIRMED' => ['label' => '✓ Shop Đã Xác Nhận', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
                            'SHIPPING' => ['label' => '🚚 Đang Giao Hàng Đến Bạn', 'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
                            'COMPLETED' => ['label' => '✨ Giao Thành Công', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                            'CANCELLED' => ['label' => '✕ Đã Hủy', 'class' => 'bg-red-50 text-red-700 border-red-200'],
                        ][$order->status] ?? ['label' => $order->status, 'class' => 'bg-stone-100 text-stone-700'];

                        if ($isQrUnpaid && $order->status !== 'CANCELLED') {
                            $statusBadge = ['label' => '⏳ Đơn hàng chưa thanh toán thành công', 'class' => 'bg-amber-50 text-amber-800 border-amber-300'];
                        }
                    @endphp
                    <div class="p-5 sm:p-6 rounded-3xl border {{ $isQrUnpaid && $order->status !== 'CANCELLED' ? 'border-amber-200' : 'border-sage-100' }} bg-[#F5FAF7] space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-sage-100 pb-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="font-mono font-bold text-base text-stone-800">#ORDER-{{ $order->id }}</span>
                                <span class="text-xs text-stone-400">Đặt lúc: {{ $order->created_at?->format('d/m/Y H:i') }}</span>
                                @if($isQrPaid)
                                    <span class="text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                        ✓ Đã thanh toán QR (Mã GD: {{ $order->transaction_code }})
                                    </span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-bold px-3.5 py-1 rounded-full border {{ $statusBadge['class'] }}">
                                    {{ $statusBadge['label'] }}
                                </span>
                                @if($isQrUnpaid && $order->status !== 'CANCELLED')
                                    <a href="{{ route('checkout.index', ['success_order_id' => $order->id]) }}"
                                        class="text-xs font-bold px-3.5 py-1 rounded-full bg-amber-600 hover:bg-amber-700 text-white transition shadow-2xs">
                                        💳 Quét QR &amp; Xác thực thanh toán →
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Danh sách sản phẩm trong đơn -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-sage-50">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img src="{{ $item->product->first_image ?? 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=400&q=80' }}"
                                            alt="{{ $item->product->name ?? '' }}"
                                            class="w-12 h-12 rounded-xl object-cover bg-sage-50 flex-shrink-0">
                                        <div class="min-w-0">
                                            <span class="font-mono text-[10px] font-bold text-sage-600">{{ $item->product->product_code ?? ('LMN-SP' . str_pad((string)$item->product_id, 3, '0', STR_PAD_LEFT)) }}</span>
                                            <a href="{{ isset($item->product->slug) ? route('products.show', $item->product->slug) : '#' }}" class="block text-xs font-bold text-stone-800 hover:text-sage-600 line-clamp-1">
                                                {{ $item->product->name ?? 'Sản phẩm #' . $item->product_id }}
                                            </a>
                                            <p class="text-[11px] text-stone-500">
                                                {{ number_format($item->price, 0, ',', '.') }}₫ x <strong>{{ $item->quantity }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                    @if($item->product)
                                        <form action="{{ route('cart.add') }}" method="POST" class="flex-shrink-0">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="text-[11px] font-semibold bg-sage-50 hover:bg-sage-500 hover:text-white text-sage-600 px-3 py-1.5 rounded-full transition">
                                                + Mua lại
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-sage-100 text-xs text-stone-600">
                            <div>
                                <span>📍 Giao đến: <strong>{{ $order->address }}</strong> ({{ $order->phone }})</span>
                                @if($order->coupon_code)
                                    <span class="ml-2 inline-block bg-terracotta-600 text-white font-semibold px-2.5 py-0.5 rounded-full border border-terracotta-500">
                                        🎟️ Voucher {{ $order->coupon_code }} (-{{ number_format($order->discount_amount, 0, ',', '.') }}₫)
                                    </span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="text-base font-bold text-sage-600">
                                    Tổng thanh toán: {{ number_format($order->total_amount, 0, ',', '.') }}₫
                                </div>
                            </div>
                        </div>

                        @php
                            $canCancelOrder = in_array($order->status, ['PENDING', 'CONFIRMED'], true) && !$order->isPaid();
                            $normalizedUserPhone = \App\Services\SmsGatewayService::normalizeVietnamPhone((string) ($user->phone ?? ''));
                            $normalizedOrderPhone = \App\Services\SmsGatewayService::normalizeVietnamPhone((string) ($order->phone ?? ''));
                            $isOwnerPhoneMatched = $normalizedUserPhone !== '' && $normalizedUserPhone === $normalizedOrderPhone;
                        @endphp
                        @if($canCancelOrder)
                            <div class="pt-3 border-t border-dashed border-red-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-red-50/50 px-4 py-3 rounded-2xl">
                                <div class="flex items-start sm:items-center gap-2 text-xs text-red-700">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-100 text-red-600 font-bold flex-shrink-0">!</span>
                                    <span>
                                        <strong>Thông báo hủy đơn:</strong> Chỉ tài khoản đăng nhập đúng SĐT chính chủ đặt hàng (<strong>{{ $order->phone }}</strong>) mới có thể hủy đơn hàng này.
                                    </span>
                                </div>

                                @if($isOwnerPhoneMatched)
                                    <form action="{{ route('customer.orders.cancel', $order) }}" method="POST"
                                          onsubmit="return handleCancelOrderSubmit(event, {{ $order->id }}, '{{ addslashes($order->phone) }}', true)"
                                          class="flex-shrink-0">
                                        @csrf
                                        <input type="hidden" name="cancel_reason" value="Khách hàng chủ động hủy đơn hàng (đã xác thực đúng SĐT chính chủ)">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-full shadow-xs transition cursor-pointer">
                                            ✕ Hủy Đơn Hàng
                                        </button>
                                    </form>
                                @else
                                    <button type="button"
                                            onclick="handleCancelOrderSubmit(event, {{ $order->id }}, '{{ addslashes($order->phone) }}', false)"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold bg-stone-200 hover:bg-red-100 text-stone-700 hover:text-red-700 border border-stone-300 px-4 py-2 rounded-full transition cursor-pointer flex-shrink-0">
                                        ✕ Hủy Đơn Hàng
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <script>
            async function handleCancelOrderSubmit(event, orderId, orderPhone, isOwnerPhoneMatched) {
                const form = event.target;
                if (form && form.dataset && form.dataset.shineConfirmed === '1') {
                    delete form.dataset.shineConfirmed;
                    return true;
                }
                event.preventDefault();

                if (!isOwnerPhoneMatched) {
                    await shineNotice({
                        type: 'warning',
                        title: 'Bảo mật hủy đơn hàng',
                        itemLabel: '#ORDER-' + orderId,
                        message: 'Bạn phải đăng nhập đúng tài khoản có số điện thoại chính chủ đặt hàng (' + orderPhone + ') mới có quyền hủy đơn hàng này!'
                    });
                    return false;
                }
                const confirmed = await shineConfirm({
                    type: 'danger',
                    title: 'Xác nhận hủy đơn hàng',
                    itemLabel: '#ORDER-' + orderId + ' • SĐT: ' + orderPhone,
                    message: 'Bạn có chắc chắn muốn hủy đơn hàng này không?',
                    detail: 'Đơn hàng sau khi hủy sẽ không thể khôi phục.',
                    confirmText: 'Xác nhận hủy đơn'
                });
                if (confirmed && form) {
                    form.dataset.shineConfirmed = '1';
                    form.submit();
                }
                return false;
            }
        </script>
    </section>
    @endif

    <!-- ========================================================================= -->
    <!-- SẢN PHẨM ĐÃ MUA TRƯỚC ĐÂY & GỢI Ý MUA SẮM (Thuộc trang Tổng Quan /tai-khoan) -->
    <!-- ========================================================================= -->
    @if($currentTab === 'overview')
    <section id="section-purchased" class="bg-white rounded-3xl border border-sage-100 p-6 sm:p-8 shadow-xs space-y-6">
        <div class="flex items-center justify-between border-b border-sage-100 pb-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Tủ Đồ Làm Đẹp Của Bạn</span>
                <h2 class="font-serif text-2xl font-bold text-stone-800">
                    💄 Sản Phẩm Bạn Đã Mua & Yêu Thích
                </h2>
            </div>
            <a href="{{ route('products.index') }}" class="text-xs font-bold text-sage-600 hover:underline">Xem toàn bộ cửa hàng →</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach(($purchasedProducts->count() > 0 ? $purchasedProducts : $featuredProducts) as $product)
                <div class="rounded-2xl border border-sage-100 bg-[#F5FAF7] overflow-hidden flex flex-col justify-between p-4 space-y-3 hover:shadow-md transition">
                    <div class="space-y-2">
                        <img src="{{ $product->first_image }}" alt="{{ $product->name }}" class="w-full h-44 object-cover rounded-xl bg-white">
                        <div class="flex items-center justify-between text-[10px]">
                            <span class="font-mono font-bold bg-stone-900 text-white px-2 py-0.5 rounded-md">{{ $product->product_code }}</span>
                            <span class="font-semibold text-emerald-600">Kho: {{ $product->stock }} SP</span>
                        </div>
                        <span class="block text-[10px] font-bold uppercase text-sage-500">{{ $product->brand->name ?? '' }} • {{ $product->skin_type }}</span>
                        <a href="{{ route('products.show', $product->slug) }}" class="block text-sm font-bold text-stone-800 hover:text-sage-600 line-clamp-2">
                            {{ $product->name }}
                        </a>
                    </div>
                    <div class="pt-2 border-t border-sage-100 flex items-center justify-between">
                        <span class="text-sm font-bold text-sage-600">{{ number_format($product->effective_price, 0, ',', '.') }}₫</span>
                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="text-xs font-semibold bg-stone-900 hover:bg-sage-500 text-white px-3.5 py-1.5 rounded-full transition">
                                + Thêm Vào Giỏ
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
