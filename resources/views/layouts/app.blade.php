<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <!-- =====================================================================
         1. CẤU HÌNH META CHUẨN SEO, OPEN GRAPH & TWITTER CARD
         ===================================================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('SHINE Beauty | Mỹ Phẩm & Chăm Sóc Da Cao Cấp Chính Hãng'))</title>
    <meta name="description" content="@yield('meta_description', 'SHINE Beauty — Hệ thống phân phối mỹ phẩm cao cấp & chăm sóc da chuẩn khoa học chính hãng 100% từ Pháp, Hàn Quốc, Nhật Bản. Miễn phí vận chuyển toàn quốc cho đơn từ 900.000₫.')">
    <meta name="keywords" content="@yield('meta_keywords', 'mỹ phẩm chính hãng, chăm sóc da, serum, kem dưỡng ẩm, chống nắng, trang điểm, nước hoa, SHINE Beauty')">
    <meta name="author" content="SHINE Beauty Cosmetics">
    <meta name="robots" content="@yield('meta_robots', 'index, follow, max-image-preview:large, max-snippet:-1')">
    <meta name="theme-color" content="#4A947A">

    <!-- Canonical URL & Đa ngôn ngữ (Hreflang) chuẩn SEO -->
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <link rel="alternate" hreflang="vi" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="en" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    <!-- Open Graph (Facebook / Zalo / Social Sharing) -->
    <meta property="og:site_name" content="SHINE Beauty Cosmetics">
    <meta property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_US' : 'vi_VN' }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', __('SHINE Beauty | Mỹ Phẩm & Chăm Sóc Da Cao Cấp Chính Hãng'))">
    <meta property="og:description" content="@yield('meta_description', 'SHINE Beauty — Hệ thống phân phối mỹ phẩm cao cấp & chăm sóc da chuẩn khoa học chính hãng 100%. Miễn phí vận chuyển toàn quốc cho đơn từ 900.000₫.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('logo.png'))">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', __('SHINE Beauty | Mỹ Phẩm & Chăm Sóc Da Cao Cấp Chính Hãng'))">
    <meta name="twitter:description" content="@yield('meta_description', 'SHINE Beauty — Hệ thống phân phối mỹ phẩm cao cấp & chăm sóc da chuẩn khoa học chính hãng 100%.')">
    <meta name="twitter:image" content="@yield('og_image', asset('logo.png'))">

    <!-- Favicon & Fonts -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=5">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}?v=5">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}?v=5">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,400&display=swap" rel="stylesheet">

    <!-- =====================================================================
         2. DỮ LIỆU CÓ CẤU TRÚC JSON-LD (SCHEMA.ORG) CHUẨN GOOGLE RICH SNIPPETS
         ===================================================================== -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@graph": [
            {
                "@@type": "Store",
                "@@id": "{{ route('home') }}#organization",
                "name": "SHINE Beauty Cosmetics",
                "url": "{{ route('home') }}",
                "logo": "{{ asset('logo.png') }}",
                "image": "{{ asset('logo.png') }}",
                "description": "Hệ thống phân phối mỹ phẩm cao cấp & chăm sóc da chuẩn khoa học chính hãng từ Pháp, Hàn Quốc và Nhật Bản.",
                "telephone": "1900-6868",
                "priceRange": "₫₫",
                "address": {
                    "@@type": "PostalAddress",
                    "streetAddress": "Đường Phạm Văn Chiêu, Phường Thông Tây Hội",
                    "addressLocality": "TP Hồ Chí Minh",
                    "addressCountry": "VN"
                }
            },
            {
                "@@type": "WebSite",
                "@@id": "{{ route('home') }}#website",
                "url": "{{ route('home') }}",
                "name": "SHINE Beauty",
                "publisher": {
                    "@@id": "{{ route('home') }}#organization"
                },
                "potentialAction": {
                    "@@type": "SearchAction",
                    "target": "{{ route('products.index') }}?search={search_term_string}",
                    "query-input": "required name=search_term_string"
                }
            }
        ]
    }
    </script>
    @stack('structured_data')
    <style>
        html, body {
            overflow-x: hidden;
            max-width: 100vw;
        }
        body, h1, h2, h3, h4, h5, h6, input, button, select, textarea {
            font-family: 'Roboto', sans-serif;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex flex-col bg-[#F5FAF7] text-stone-800 overflow-x-hidden">
    @include('partials.loading-animation')
    @php
        $cart = session('cart', []);
        $cartCount = collect($cart)->sum('quantity');
        $cartTotal = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
    @endphp

    <!-- =====================================================================
         3. THANH THÔNG BÁO ĐẦU TRANG (TOP ANNOUNCEMENT BAR)
         ===================================================================== -->
    <div class="bg-gradient-to-r from-sage-200/80 via-sage-100 to-sage-200/80 text-sage-900 text-xs py-2 px-4 text-center font-medium tracking-wide">
        {{ __('✨ SHINE Beauty — Miễn phí vận chuyển toàn quốc cho đơn từ 900.000₫ — Cam kết 100% Mỹ phẩm Chính hãng ✨') }}
    </div>

    <!-- =====================================================================
         4. THANH ĐIỀU HƯỚNG CHÍNH (HEADER NAVIGATION BAR CHUẨN SEO SEMANTIC)
         - Gồm: Logo thương hiệu, Menu chính, Chuyển đổi ngôn ngữ (VI/EN),
           Nút Hồ sơ hiển thị Tên khách hàng và Nút mở Giỏ hàng nhanh.
         ===================================================================== -->
    <header role="banner" class="sticky top-0 z-40 bg-[#F5FAF7]/95 backdrop-blur-md border-b border-sage-100">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20 gap-2">
                <!-- Logo Thương Hiệu -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 sm:gap-3 group min-w-0 flex-shrink-0" aria-label="SHINE Beauty - Trang chủ">
                    <img src="{{ asset('logo.png') }}?v=4" alt="SHINE Beauty Logo" width="64" height="64" class="w-10 h-10 sm:w-14 sm:h-14 lg:w-16 lg:h-16 object-contain drop-shadow-xs group-hover:scale-105 transition flex-shrink-0">
                    <div class="min-w-0">
                        <span class="font-serif text-lg sm:text-2xl lg:text-3xl font-bold tracking-tight text-stone-800 block leading-tight truncate">
                            SHINE <span class="text-sage-500 font-normal italic">Beauty</span>
                        </span>
                        <p class="text-[9px] sm:text-[10px] uppercase tracking-[0.18em] sm:tracking-[0.2em] text-stone-400 -mt-0.5 truncate">
                            Luxury Cosmetics
                        </p>
                    </div>
                </a>

                <!-- Menu Điều Hướng Chính (Desktop Navigation: >= 1024px) -->
                <nav aria-label="Điều hướng chính" class="hidden lg:flex items-center gap-4 xl:gap-6 text-sm font-medium flex-shrink-0">
                    <a href="{{ route('home') }}" class="whitespace-nowrap {{ request()->routeIs('home') ? 'text-sage-600 font-semibold' : 'text-stone-700 hover:text-sage-500' }}">{{ __('Trang Chủ') }}</a>
                    <a href="{{ route('products.index') }}" class="whitespace-nowrap {{ request()->routeIs('products.*') && !request('category') ? 'text-sage-600 font-semibold' : 'text-stone-700 hover:text-sage-500' }}">{{ __('Sản Phẩm') }}</a>
                    <a href="{{ route('products.index', ['category' => 'cham-soc-da']) }}" class="whitespace-nowrap {{ request('category') === 'cham-soc-da' ? 'text-sage-600 font-semibold' : 'text-stone-700 hover:text-sage-500' }}">{{ __('Chăm Sóc Da') }}</a>
                    <a href="{{ route('blog.index') }}" class="whitespace-nowrap {{ request()->routeIs('blog.*') ? 'text-sage-600 font-semibold' : 'text-stone-700 hover:text-sage-500' }}">{{ __('Blog Làm Đẹp') }}</a>
                    <a href="{{ route('about') }}" class="whitespace-nowrap {{ request()->routeIs('about') ? 'text-sage-600 font-semibold' : 'text-stone-700 hover:text-sage-500' }}">{{ __('Về Chúng Tôi') }}</a>
                    <a href="{{ route('orders.track') }}" class="whitespace-nowrap {{ request()->routeIs('orders.track') ? 'text-sage-600 font-semibold' : 'text-stone-700 hover:text-sage-500' }}">{{ __('Tra Cứu Đơn Hàng') }}</a>
                </nav>

                <!-- Cụm Chức Năng Phải: Ngôn Ngữ (VI/EN) + Hồ Sơ Khách Hàng + Giỏ Hàng + Nút Menu Mobile/Tablet -->
                <div class="flex items-center gap-1.5 sm:gap-2.5 flex-shrink-0">
                    <!-- Bộ chuyển đổi ngôn ngữ VI / EN -->
                    <div class="inline-flex items-center rounded-full bg-white border border-sage-200 p-0.5 shadow-2xs text-[11px] sm:text-xs font-bold" role="group" aria-label="Chuyển đổi ngôn ngữ">
                        <a href="{{ route('lang.switch', 'vi') }}"
                           title="Tiếng Việt (Mặc định)"
                           class="px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-full transition flex items-center gap-1 {{ app()->getLocale() === 'vi' ? 'bg-[#DA251D] text-white shadow-2xs' : 'text-stone-600 hover:text-[#DA251D]' }}">
                            <svg class="w-3.5 h-2.5 sm:w-4 sm:h-3 rounded-[2px] shadow-2xs flex-shrink-0 {{ app()->getLocale() === 'vi' ? 'ring-1 ring-white/60' : '' }}" viewBox="0 0 30 20" xmlns="http://www.w3.org/2000/svg">
                                <rect width="30" height="20" fill="#DA251D"/>
                                <polygon points="15,4 16.47,8.52 21.22,8.52 17.38,11.31 18.85,15.83 15,13.04 11.15,15.83 12.62,11.31 8.78,8.52 13.53,8.52" fill="#FFFF00"/>
                            </svg>
                            <span>VI</span>
                        </a>
                        <a href="{{ route('lang.switch', 'en') }}"
                           title="Switch to English (Global)"
                           class="px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-full transition flex items-center gap-1 {{ app()->getLocale() === 'en' ? 'bg-[#0284C7] text-white shadow-2xs' : 'text-stone-600 hover:text-[#0284C7]' }}">
                            <svg class="w-3.5 h-2.5 sm:w-4 sm:h-3 rounded-[2px] shadow-2xs flex-shrink-0 {{ app()->getLocale() === 'en' ? 'ring-1 ring-white/60' : '' }}" viewBox="0 0 30 20" xmlns="http://www.w3.org/2000/svg">
                                <rect width="30" height="20" fill="#0284C7"/>
                                <circle cx="15" cy="10" r="6.5" fill="none" stroke="#FFFFFF" stroke-width="1.3"/>
                                <ellipse cx="15" cy="10" rx="3.2" ry="6.5" fill="none" stroke="#FFFFFF" stroke-width="1.2"/>
                                <line x1="8.5" y1="10" x2="21.5" y2="10" stroke="#FFFFFF" stroke-width="1.2"/>
                                <path d="M9.5,6.8 Q15,8 20.5,6.8 M9.5,13.2 Q15,12 20.5,13.2" fill="none" stroke="#FFFFFF" stroke-width="1"/>
                            </svg>
                            <span>EN</span>
                        </a>
                    </div>

                    <!-- Nút Hồ sơ tài khoản khách hàng -->
                    @if(auth()->check() && !auth()->user()->is_admin)
                        <a href="{{ route('customer.account') }}"
                           class="inline-flex items-center gap-1.5 text-xs font-bold {{ request()->routeIs('customer.*') ? 'bg-sage-600' : 'bg-sage-500 hover:bg-sage-600' }} text-white px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-full transition shadow-xs"
                           title="Tài khoản của {{ auth()->user()->name }}">
                            <span class="text-sm">👤</span>
                            <span class="hidden sm:inline max-w-[110px] md:max-w-[160px] truncate">{{ auth()->user()->name }}</span>
                        </a>
                    @else
                        <button type="button" onclick="openAuthModal('login')"
                                class="inline-flex items-center gap-1.5 text-xs font-bold bg-sage-100 hover:bg-sage-200 text-sage-800 px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-full transition border border-sage-200 cursor-pointer"
                                title="{{ __('Đăng Nhập / Đăng Ký') }}">
                            <span class="text-sm">👤</span>
                            <span class="hidden sm:inline">{{ __('Đăng Nhập / Đăng Ký') }}</span>
                        </button>
                    @endif

                    <!-- Nút mở Ngăn kéo Giỏ hàng (Slide-over Cart Drawer) -->
                    <button type="button" onclick="document.getElementById('cart-drawer').classList.remove('hidden')" class="relative flex items-center gap-1.5 sm:gap-2 bg-sage-50 hover:bg-sage-100 text-sage-700 px-2.5 sm:px-4 py-1.5 sm:py-2.5 rounded-full font-medium text-xs sm:text-sm transition shadow-sm border border-sage-200/60 cursor-pointer">
                        <span>🛍️</span>
                        <span class="hidden sm:inline">{{ __('Giỏ Hàng') }}</span>
                        <span class="bg-sage-500 text-white text-[11px] sm:text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">
                            {{ $cartCount }}
                        </span>
                    </button>

                    <!-- Nút mở Menu Mobile & Tablet (< 1024px) -->
                    <button type="button"
                            id="mobile-menu-toggle"
                            onclick="document.getElementById('mobile-nav-drawer').classList.toggle('hidden')"
                            aria-label="Mở menu điều hướng"
                            class="lg:hidden inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white hover:bg-sage-50 text-stone-700 border border-sage-200 shadow-2xs transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Thanh Điều Hướng Nhanh Trên Mobile & Tablet (< 1024px) - Cuộn ngang mượt mà -->
            <nav aria-label="Điều hướng nhanh trên thiết bị di động" class="lg:hidden flex items-center gap-2 overflow-x-auto no-scrollbar py-2 border-t border-sage-100/80 text-xs font-semibold whitespace-nowrap">
                <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-full transition {{ request()->routeIs('home') ? 'bg-sage-500 text-white shadow-2xs' : 'bg-white text-stone-700 border border-sage-100 hover:bg-sage-50' }}">
                    {{ __('Trang Chủ') }}
                </a>
                <a href="{{ route('products.index') }}" class="px-3 py-1.5 rounded-full transition {{ request()->routeIs('products.*') && !request('category') ? 'bg-sage-500 text-white shadow-2xs' : 'bg-white text-stone-700 border border-sage-100 hover:bg-sage-50' }}">
                    {{ __('Sản Phẩm') }}
                </a>
                <a href="{{ route('products.index', ['category' => 'cham-soc-da']) }}" class="px-3 py-1.5 rounded-full transition {{ request('category') === 'cham-soc-da' ? 'bg-sage-500 text-white shadow-2xs' : 'bg-white text-stone-700 border border-sage-100 hover:bg-sage-50' }}">
                    {{ __('Chăm Sóc Da') }}
                </a>
                <a href="{{ route('blog.index') }}" class="px-3 py-1.5 rounded-full transition {{ request()->routeIs('blog.*') ? 'bg-sage-500 text-white shadow-2xs' : 'bg-white text-stone-700 border border-sage-100 hover:bg-sage-50' }}">
                    {{ __('Blog Làm Đẹp') }}
                </a>
                <a href="{{ route('about') }}" class="px-3 py-1.5 rounded-full transition {{ request()->routeIs('about') ? 'bg-sage-500 text-white shadow-2xs' : 'bg-white text-stone-700 border border-sage-100 hover:bg-sage-50' }}">
                    {{ __('Về Chúng Tôi') }}
                </a>
                <a href="{{ route('orders.track') }}" class="px-3 py-1.5 rounded-full transition {{ request()->routeIs('orders.track') ? 'bg-sage-500 text-white shadow-2xs' : 'bg-white text-stone-700 border border-sage-100 hover:bg-sage-50' }}">
                    {{ __('Tra Cứu Đơn Hàng') }}
                </a>
            </nav>

            <!-- Menu Mở Rộng Cho Mobile & Tablet (#mobile-nav-drawer) -->
            <div id="mobile-nav-drawer" class="hidden lg:hidden py-3 border-t border-sage-200/80 space-y-2">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs font-semibold">
                    <a href="{{ route('home') }}" class="p-2.5 rounded-xl bg-white border border-sage-100 text-stone-800 hover:bg-sage-50 flex items-center gap-2">
                        <span>🏠</span> <span>{{ __('Trang Chủ') }}</span>
                    </a>
                    <a href="{{ route('products.index') }}" class="p-2.5 rounded-xl bg-white border border-sage-100 text-stone-800 hover:bg-sage-50 flex items-center gap-2">
                        <span>✨</span> <span>{{ __('Tất Cả Sản Phẩm') }}</span>
                    </a>
                    <a href="{{ route('products.index', ['category' => 'cham-soc-da']) }}" class="p-2.5 rounded-xl bg-white border border-sage-100 text-stone-800 hover:bg-sage-50 flex items-center gap-2">
                        <span>🌿</span> <span>{{ __('Chăm Sóc Da') }}</span>
                    </a>
                    <a href="{{ route('products.index', ['category' => 'trang-diem']) }}" class="p-2.5 rounded-xl bg-white border border-sage-100 text-stone-800 hover:bg-sage-50 flex items-center gap-2">
                        <span>💄</span> <span>Trang Điểm</span>
                    </a>
                    <a href="{{ route('blog.index') }}" class="p-2.5 rounded-xl bg-white border border-sage-100 text-stone-800 hover:bg-sage-50 flex items-center gap-2">
                        <span>📖</span> <span>{{ __('Blog Làm Đẹp') }}</span>
                    </a>
                    <a href="{{ route('about') }}" class="p-2.5 rounded-xl bg-white border border-sage-100 text-stone-800 hover:bg-sage-50 flex items-center gap-2">
                        <span>💎</span> <span>{{ __('Về Chúng Tôi') }}</span>
                    </a>
                    <a href="{{ route('orders.track') }}" class="p-2.5 rounded-xl bg-white border border-sage-100 text-stone-800 hover:bg-sage-50 flex items-center gap-2">
                        <span>📦</span> <span>{{ __('Tra Cứu Đơn Hàng') }}</span>
                    </a>
                </div>
                @if(auth()->check() && !auth()->user()->is_admin)
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-sage-100 text-xs">
                        <a href="{{ route('customer.account') }}" class="font-bold text-sage-700 hover:underline">
                            👤 Hồ sơ: {{ auth()->user()->name }} →
                        </a>
                        <a href="{{ route('customer.wishlist') }}" class="font-bold text-red-500 hover:underline">
                            ❤️ Yêu thích ({{ count($wishlistProductIds ?? []) }})
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </header>

    <!-- =====================================================================
         5. THÔNG BÁO HỆ THỐNG (FLASH NOTIFICATIONS)
         ===================================================================== -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4" role="status">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-2xl text-sm font-medium flex items-center justify-between shadow-sm">
                <span>✓ {{ session('success') }}</span>
                <a href="{{ route('checkout.index') }}" class="text-xs font-bold text-sage-600 hover:underline ml-4">Xem giỏ hàng & Thanh toán →</a>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4" role="status">
            <div class="bg-amber-50 border border-amber-300 text-amber-900 px-5 py-3.5 rounded-2xl text-sm font-medium flex items-center justify-between shadow-sm">
                <span>⏳ {{ session('warning') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4" role="alert">
            <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-2xl text-sm font-medium">
                ⚠ {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- =====================================================================
         6. NỘI DUNG CHÍNH CỦA TRANG (MAIN CONTENT LANDMARK CHUẨN SEO)
         ===================================================================== -->
    <main id="main-content" role="main" class="flex-1">
        @yield('content')
    </main>

    <!-- =====================================================================
         7. NGĂN KÉO GIỎ HÀNG TRƯỢT NGANG (SLIDE-OVER CART DRAWER)
         - Cho phép tăng (+) / giảm (−) số lượng từng sản phẩm ngay trong giỏ.
         - Thanh tiến trình ưu đãi miễn phí vận chuyển dùng màu Terracotta nổi bật
           khi đơn hàng chưa đạt 900.000₫.
         ===================================================================== -->
    <div id="cart-drawer" class="fixed inset-0 z-50 {{ session('open_cart_drawer') ? '' : 'hidden' }}">
        <div class="fixed inset-0 bg-stone-900/40 backdrop-blur-xs" onclick="document.getElementById('cart-drawer').classList.add('hidden')"></div>
        <aside class="fixed top-0 right-0 h-full w-full max-w-md bg-[#F5FAF7] shadow-2xl border-l border-sage-100 flex flex-col justify-between z-10">
            <div class="p-6 border-b border-sage-100 flex items-center justify-between bg-white">
                <div>
                    <h2 class="font-serif text-xl font-bold text-stone-800">Giỏ Hàng Mỹ Phẩm</h2>
                    <p class="text-xs text-stone-500">{{ $cartCount }} sản phẩm đã chọn</p>
                </div>
                <button type="button" onclick="document.getElementById('cart-drawer').classList.add('hidden')" class="p-2 rounded-full hover:bg-sage-50 text-stone-500 font-bold">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                @forelse($cart as $item)
                    <div class="flex gap-4 bg-white p-3.5 rounded-2xl border border-sage-100 shadow-xs">
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-20 h-20 rounded-xl object-cover bg-sage-50 flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-bold uppercase text-sage-500">{{ $item['brand_name'] ?? 'Shine' }}</p>
                            <a href="{{ route('products.show', $item['slug']) }}" class="text-sm font-semibold text-stone-800 hover:text-sage-600 line-clamp-1">{{ $item['name'] }}</a>
                            <p class="text-xs text-stone-400">{{ $item['volume'] }} • {{ $item['skin_type'] }}</p>
                            <p class="text-sm font-bold text-sage-600 mt-1">
                                {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}₫
                                @if($item['quantity'] > 1)
                                    <span class="text-[11px] font-normal text-stone-400">({{ number_format($item['price'], 0, ',', '.') }}₫ x {{ $item['quantity'] }})</span>
                                @endif
                            </p>
                            <div class="flex items-center justify-between mt-2">
                                <div class="inline-flex items-center border border-sage-200 rounded-full bg-[#F5FAF7] px-1 py-0.5">
                                    <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                        <input type="hidden" name="open_drawer" value="1">
                                        <button type="submit" title="Giảm 1 sản phẩm" class="w-6 h-6 rounded-full hover:bg-sage-200/70 text-stone-700 hover:text-sage-700 font-bold text-xs flex items-center justify-center transition cursor-pointer">−</button>
                                    </form>
                                    <span class="w-7 text-center text-xs font-bold text-stone-800">{{ $item['quantity'] }}</span>
                                    <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                        <input type="hidden" name="open_drawer" value="1">
                                        <button type="submit" title="Tăng 1 sản phẩm" class="w-6 h-6 rounded-full hover:bg-sage-200/70 text-stone-700 hover:text-sage-700 font-bold text-xs flex items-center justify-center transition cursor-pointer">+</button>
                                    </form>
                                </div>
                                <form action="{{ route('cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                    <input type="hidden" name="open_drawer" value="1">
                                    <button type="submit" class="text-xs text-stone-400 hover:text-red-500 cursor-pointer">Xóa</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16 space-y-3">
                        <p class="font-serif text-lg font-semibold text-stone-700">Giỏ hàng của bạn đang trống</p>
                        <p class="text-xs text-stone-400">Hãy khám phá các dòng serum, kem dưỡng và son môi chính hãng.</p>
                    </div>
                @endforelse
            </div>

            @if(count($cart) > 0)
                @php
                    $drawerFreeShipThreshold = \App\Services\CheckoutService::FREE_SHIPPING_THRESHOLD;
                    $drawerStandardFee = \App\Services\CheckoutService::STANDARD_SHIPPING_FEE;
                    $drawerShippingFee = $cartTotal < $drawerFreeShipThreshold ? $drawerStandardFee : 0;
                    $drawerFreeShipDiff = max(0, $drawerFreeShipThreshold - $cartTotal);
                    $drawerProgressPercent = min(100, (int) round(($cartTotal / $drawerFreeShipThreshold) * 100));
                @endphp
                <div class="p-6 bg-white border-t border-sage-100 space-y-3.5">
                    <div class="{{ $drawerFreeShipDiff > 0 ? 'bg-terracotta-50/80 border-terracotta-200' : 'bg-[#F5FAF7] border-sage-100' }} border rounded-2xl p-3 space-y-1.5 text-xs">
                        @if($drawerFreeShipDiff > 0)
                            <p class="text-terracotta-900">
                                🚚 Mua thêm <strong class="text-terracotta-600 font-bold">{{ number_format($drawerFreeShipDiff, 0, ',', '.') }}₫</strong> để được <strong>Miễn phí vận chuyển toàn quốc</strong> (đơn từ {{ number_format($drawerFreeShipThreshold, 0, ',', '.') }}₫)
                            </p>
                        @else
                            <p class="text-emerald-700 font-semibold">
                                ✨ Đơn hàng đủ điều kiện <strong>Miễn phí vận chuyển toàn quốc</strong>!
                            </p>
                        @endif
                        <div class="w-full h-2 {{ $drawerFreeShipDiff > 0 ? 'bg-terracotta-100' : 'bg-sage-100' }} rounded-full overflow-hidden">
                            <div class="h-full {{ $drawerFreeShipDiff > 0 ? 'bg-terracotta-600' : 'bg-sage-500' }} rounded-full transition-all duration-300" style="width: {{ $drawerProgressPercent }}%"></div>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-xs text-stone-500">
                        <div class="flex items-center justify-between">
                            <span>Tạm tính:</span>
                            <span class="font-semibold text-stone-700">{{ number_format($cartTotal, 0, ',', '.') }}₫</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Phí vận chuyển toàn quốc:</span>
                            @if($drawerShippingFee > 0)
                                <span class="font-semibold text-amber-600">+{{ number_format($drawerShippingFee, 0, ',', '.') }}₫</span>
                            @else
                                <span class="font-semibold text-emerald-600">Miễn phí (0₫)</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-base font-bold pt-2 border-t border-sage-100">
                        <span>Tổng dự kiến:</span>
                        <span class="text-sage-600 text-lg">{{ number_format($cartTotal + $drawerShippingFee, 0, ',', '.') }}₫</span>
                    </div>
                    <a href="{{ route('checkout.index') }}" class="block w-full text-center bg-sage-500 hover:bg-sage-600 text-white font-semibold py-3.5 rounded-full shadow-md shadow-sage-200 transition">
                        Tiến Hành Thanh Toán →
                    </a>
                </div>
            @endif
        </aside>
    </div>

    <!-- Auth Popup / Modal (Login & Sign Up) -->
    @if(!auth()->check() || auth()->user()->is_admin)
        @php
            $initialAuthTab = old('auth_tab', session('open_auth_modal', request('auth', 'login')));
            if (!in_array($initialAuthTab, ['login', 'register'], true)) {
                $initialAuthTab = 'login';
            }
            $hasAuthErrors = $errors->hasAny(['login', 'password', 'name', 'username', 'email', 'phone', 'address']);
            $shouldAutoOpenAuthModal = $hasAuthErrors || session()->has('open_auth_modal') || in_array(request('auth'), ['login', 'register'], true);
        @endphp
        <div id="auth-modal" class="fixed inset-0 z-50 {{ $shouldAutoOpenAuthModal ? 'flex' : 'hidden' }} items-center justify-center p-4 sm:p-6 overflow-y-auto">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs transition-opacity" onclick="closeAuthModal()"></div>

            <!-- Modal Box -->
            <div class="relative w-full max-w-lg bg-white rounded-3xl border border-sage-100 shadow-2xl overflow-hidden z-10 my-auto">
                <!-- Modal Header Banner -->
                <div class="bg-gradient-to-r from-sage-500 to-sage-600 px-6 py-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/95 p-1.5 shadow-sm flex items-center justify-center flex-shrink-0">
                            <img src="{{ asset('logo.png') }}?v=4" alt="SHINE Beauty Logo" width="40" height="40" class="w-10 h-10 object-contain">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-sage-100 block">Thành Viên SHINE Beauty</span>
                            <h2 id="auth-modal-title" class="font-serif text-xl font-bold">
                                {{ $initialAuthTab === 'register' ? 'Đăng Ký Tài Khoản Mới' : 'Đăng Nhập Khách Hàng' }}
                            </h2>
                        </div>
                    </div>
                    <button type="button" onclick="closeAuthModal()" class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center font-bold transition cursor-pointer" aria-label="Đóng">
                        ✕
                    </button>
                </div>

                <!-- Tab Switcher (Login / Sign Up) -->
                <div class="px-6 pt-5">
                    <div class="grid grid-cols-2 p-1 bg-sage-50/80 rounded-2xl border border-sage-100 text-xs font-bold">
                        <button type="button" id="auth-tab-btn-login" onclick="switchAuthTab('login')"
                            class="py-2.5 rounded-xl transition cursor-pointer {{ $initialAuthTab === 'login' ? 'bg-sage-500 text-white shadow-xs' : 'text-stone-600 hover:text-sage-600' }}">
                            🔑 Đăng Nhập
                        </button>
                        <button type="button" id="auth-tab-btn-register" onclick="switchAuthTab('register')"
                            class="py-2.5 rounded-xl transition cursor-pointer {{ $initialAuthTab === 'register' ? 'bg-sage-500 text-white shadow-xs' : 'text-stone-600 hover:text-sage-600' }}">
                            ✨ Đăng Ký Mới
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 max-h-[82vh] overflow-y-auto">
                    @if($hasAuthErrors)
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-xs font-medium">
                            ⚠ {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- Equal-size Grid Stack for Login & Register -->
                    <div class="grid grid-cols-1">
                        <!-- TAB 1: LOGIN FORM -->
                        <div id="auth-panel-login" class="col-start-1 row-start-1 flex flex-col justify-between h-full transition-opacity duration-150 {{ $initialAuthTab === 'login' ? 'opacity-100 visible pointer-events-auto z-10' : 'opacity-0 invisible pointer-events-none z-0' }}">
                            <div class="space-y-4">
                                <p class="text-xs text-stone-500">
                                    Đăng nhập bằng <strong>Tên đăng nhập</strong> hoặc <strong>Email</strong> để xem đơn hàng, giỏ hàng đã lưu và kho voucher ưu đãi.
                                </p>

                                <form id="auth-form-login" action="{{ route('customer.login.submit') }}" method="POST" class="space-y-3.5 text-xs">
                                    @csrf
                                    <input type="hidden" name="auth_tab" value="login">

                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone-600 mb-1">
                                            Tên đăng nhập hoặc Email *
                                        </label>
                                        <input type="text" id="modal-cust-login" name="login" required value="{{ old('login') }}"
                                            placeholder="Nhập tên đăng nhập hoặc email của bạn"
                                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone-600 mb-1">
                                            Mật khẩu *
                                        </label>
                                        <input type="password" id="modal-cust-password" name="password" required
                                            placeholder="••••••••••••"
                                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                                    </div>

                                    <div class="flex items-center justify-between text-xs text-stone-500 pt-0.5">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="remember" checked class="rounded accent-sage-500">
                                            <span>Tự động lưu giỏ hàng & phiên đăng nhập</span>
                                        </label>
                                        <a href="{{ route('customer.password.request') }}" class="text-sage-500 hover:text-sage-600 hover:underline font-semibold">
                                            Quên Mật Khẩu?
                                        </a>
                                    </div>

                                    <!-- Member Perks Box to balance visual density with Register form -->
                                    <div class="rounded-2xl bg-sage-50/70 border border-sage-100 p-3.5 space-y-1.5 text-xs text-stone-600">
                                        <p class="font-bold text-sage-600 text-[11px] uppercase tracking-wider">✦ Quyền lợi thành viên Shine</p>
                                        <p class="text-[11px] leading-relaxed">- Giỏ hàng thông minh: Tự động lưu trữ và khôi phục giỏ hàng khi đăng nhập lại.</p>
                                        <p class="text-[11px] leading-relaxed">- Ưu đãi độc quyền: Nhanh chóng áp dụng các mã giảm giá dành riêng cho thành viên như LUMINA20, FLASHSALE30.</p>
                                    </div>
                                </form>
                            </div>

                            <div class="space-y-3.5 pt-3">
                                <button type="submit" form="auth-form-login" class="w-full bg-sage-500 hover:bg-sage-600 text-white font-semibold py-3.5 rounded-2xl shadow-md shadow-sage-200 transition text-sm cursor-pointer">
                                    Đăng Nhập Vào Tài Khoản Khách Hàng →
                                </button>

                                <div class="pt-3 border-t border-sage-100 flex items-center justify-between text-xs text-stone-500">
                                    <span>Chưa có tài khoản thành viên?</span>
                                    <button type="button" onclick="switchAuthTab('register')" class="font-bold text-sage-600 hover:underline cursor-pointer">
                                        Đăng ký tài khoản mới →
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: REGISTER (SIGN UP) FORM -->
                        <div id="auth-panel-register" class="col-start-1 row-start-1 flex flex-col justify-between h-full transition-opacity duration-150 {{ $initialAuthTab === 'register' ? 'opacity-100 visible pointer-events-auto z-10' : 'opacity-0 invisible pointer-events-none z-0' }}">
                            <div class="space-y-4">
                                <p class="text-xs text-stone-500">
                                    Tạo tài khoản miễn phí để lưu giỏ hàng tự động, theo dõi vận chuyển và nhận ngay mã giảm giá thành viên mới.
                                </p>

                                <form id="auth-form-register" action="{{ route('customer.register') }}" method="POST" class="space-y-3.5 text-xs">
                                    @csrf
                                    <input type="hidden" name="auth_tab" value="register">

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone-600 mb-1">Họ và tên *</label>
                                            <input type="text" name="name" required value="{{ old('name') }}" placeholder="Họ và tên *"
                                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone-600 mb-1">Tên đăng nhập *</label>
                                            <input type="text" name="username" required value="{{ old('username') }}" placeholder="Tên đăng nhập *"
                                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone-600 mb-1">Email *</label>
                                            <input type="email" name="email" required value="{{ old('email') }}" placeholder="Email *"
                                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone-600 mb-1">Số điện thoại *</label>
                                            <div class="flex gap-1.5">
                                                <input type="text" id="reg-phone-input" name="phone" required value="{{ old('phone') }}" placeholder="Số điện thoại *"
                                                    class="flex-1 min-w-0 px-3 py-2.5 text-sm rounded-xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                                                <button type="button" id="btn-send-reg-otp" onclick="sendRegistrationOtp()"
                                                    class="px-3 py-2 rounded-xl bg-stone-900 hover:bg-sage-600 text-white font-bold text-[11px] whitespace-nowrap transition cursor-pointer">
                                                    📲 Gửi OTP
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Khung nhập và xác thực OTP số điện thoại (3 phút, tối đa 5 lần thử) -->
                                    <div id="reg-otp-box" class="hidden p-3 rounded-xl border border-sage-200 bg-sage-50/70 space-y-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <span id="reg-otp-status-msg" class="text-[11px] font-semibold text-sage-800">
                                                Nhập mã OTP 6 số (hiệu lực 3 phút):
                                            </span>
                                            <span id="reg-otp-verified-badge" class="hidden text-[11px] font-bold bg-emerald-600 text-white px-2.5 py-0.5 rounded-full">
                                                ✓ Đã xác thực OTP
                                            </span>
                                        </div>
                                        <div class="flex gap-2">
                                            <input type="text" id="reg-otp-code-input" maxlength="6" placeholder="Nhập 6 chữ số OTP..."
                                                class="flex-1 px-3 py-2 text-xs font-mono font-bold tracking-widest rounded-lg border border-sage-300 bg-white focus:outline-none">
                                            <button type="button" id="btn-verify-reg-otp" onclick="verifyRegistrationOtp()"
                                                class="px-3.5 py-2 rounded-lg bg-sage-600 hover:bg-sage-700 text-white font-bold text-xs transition cursor-pointer">
                                                Xác thực OTP
                                            </button>
                                        </div>
                                        <input type="hidden" name="verification_token" id="reg-otp-verification-token" value="">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone-600 mb-1">Địa chỉ nhận hàng</label>
                                        <input type="text" name="address" value="{{ old('address') }}" placeholder="Địa chỉ nhận hàng mặc định"
                                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-stone-600 mb-1">Mật khẩu *</label>
                                        <input type="password" name="password" required placeholder="Mật khẩu (tối thiểu 6 ký tự) *"
                                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-sage-200 bg-[#F5FAF7] focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-400">
                                    </div>
                                </form>
                            </div>

                            <div class="space-y-3.5 pt-3">
                                <button type="submit" form="auth-form-register" class="w-full bg-sage-500 hover:bg-sage-600 text-white font-semibold py-3.5 rounded-2xl shadow-md shadow-sage-200 transition text-sm cursor-pointer">
                                    Tạo Tài Khoản Khách Hàng Mới →
                                </button>

                                <div class="pt-3 border-t border-sage-100 flex items-center justify-between text-xs text-stone-500">
                                    <span>Đã có tài khoản thành viên?</span>
                                    <button type="button" onclick="switchAuthTab('login')" class="font-bold text-sage-600 hover:underline cursor-pointer">
                                        ← Quay lại Đăng nhập
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function openAuthModal(tab = 'login') {
                const modal = document.getElementById('auth-modal');
                if (!modal) return;
                switchAuthTab(tab);
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function closeAuthModal() {
                const modal = document.getElementById('auth-modal');
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }

            let regOtpCooldownInterval = null;
            async function sendRegistrationOtp() {
                const phoneInput = document.getElementById('reg-phone-input');
                const btnSend = document.getElementById('btn-send-reg-otp');
                const otpBox = document.getElementById('reg-otp-box');
                const statusMsg = document.getElementById('reg-otp-status-msg');
                const otpInput = document.getElementById('reg-otp-code-input');
                if (!phoneInput || !phoneInput.value.trim()) {
                    alert('Vui lòng nhập số điện thoại trước khi yêu cầu gửi mã OTP.');
                    return;
                }

                btnSend.disabled = true;
                try {
                    const res = await fetch('/api/auth/otp/send', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ phone: phoneInput.value.trim() })
                    });
                    const data = await res.json();
                    if (otpBox) otpBox.classList.remove('hidden');

                    if (!res.ok || !data.ok) {
                        if (statusMsg) {
                            statusMsg.textContent = '⚠ ' + (data.message || 'Không thể gửi mã OTP.');
                            statusMsg.className = 'text-[11px] font-semibold text-red-600';
                        }
                        if (data.retry_after) {
                            startRegOtpCooldown(btnSend, parseInt(data.retry_after, 10));
                        } else {
                            btnSend.disabled = false;
                        }
                        return;
                    }

                    let msg = '✓ ' + data.message;
                    if (data.dev_otp_preview) {
                        msg += ' (Mã test Dev: ' + data.dev_otp_preview + ')';
                        if (otpInput) otpInput.value = data.dev_otp_preview;
                    }
                    if (statusMsg) {
                        statusMsg.textContent = msg;
                        statusMsg.className = 'text-[11px] font-semibold text-sage-800';
                    }
                    startRegOtpCooldown(btnSend, parseInt(data.cooldown_seconds || 60, 10));
                } catch (e) {
                    btnSend.disabled = false;
                    alert('Lỗi kết nối khi gửi mã OTP.');
                }
            }

            function startRegOtpCooldown(btn, seconds) {
                if (regOtpCooldownInterval) clearInterval(regOtpCooldownInterval);
                let remaining = seconds;
                btn.disabled = true;
                btn.textContent = 'Đợi ' + remaining + 's';
                regOtpCooldownInterval = setInterval(() => {
                    remaining--;
                    if (remaining <= 0) {
                        clearInterval(regOtpCooldownInterval);
                        btn.disabled = false;
                        btn.textContent = '📲 Gửi lại OTP';
                    } else {
                        btn.textContent = 'Đợi ' + remaining + 's';
                    }
                }, 1000);
            }

            async function verifyRegistrationOtp() {
                const phoneInput = document.getElementById('reg-phone-input');
                const otpInput = document.getElementById('reg-otp-code-input');
                const statusMsg = document.getElementById('reg-otp-status-msg');
                const verifiedBadge = document.getElementById('reg-otp-verified-badge');
                const tokenInput = document.getElementById('reg-otp-verification-token');

                if (!phoneInput || !otpInput || !otpInput.value.trim()) {
                    alert('Vui lòng nhập mã OTP 6 chữ số.');
                    return;
                }

                try {
                    const res = await fetch('/api/auth/otp/verify', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({
                            phone: phoneInput.value.trim(),
                            otp: otpInput.value.trim()
                        })
                    });
                    const data = await res.json();
                    if (!res.ok || !data.ok) {
                        if (statusMsg) {
                            statusMsg.textContent = '⚠ ' + (data.message || 'Mã OTP không chính xác.');
                            statusMsg.className = 'text-[11px] font-semibold text-red-600';
                        }
                        return;
                    }
                    if (statusMsg) {
                        statusMsg.textContent = '✓ ' + data.message;
                        statusMsg.className = 'text-[11px] font-semibold text-emerald-700';
                    }
                    if (verifiedBadge) verifiedBadge.classList.remove('hidden');
                    if (tokenInput && data.verification_token) {
                        tokenInput.value = data.verification_token;
                    }
                } catch (e) {
                    alert('Lỗi kết nối khi xác thực mã OTP.');
                }
            }

            function switchAuthTab(tab) {
                const loginPanel = document.getElementById('auth-panel-login');
                const registerPanel = document.getElementById('auth-panel-register');
                const loginBtn = document.getElementById('auth-tab-btn-login');
                const registerBtn = document.getElementById('auth-tab-btn-register');
                const modalTitle = document.getElementById('auth-modal-title');
                const isEn = document.documentElement.lang === 'en';

                if (!loginPanel || !registerPanel) return;

                const activePanelClasses = 'col-start-1 row-start-1 flex flex-col justify-between h-full transition-opacity duration-150 opacity-100 visible pointer-events-auto z-10';
                const inactivePanelClasses = 'col-start-1 row-start-1 flex flex-col justify-between h-full transition-opacity duration-150 opacity-0 invisible pointer-events-none z-0';

                if (tab === 'register') {
                    loginPanel.className = inactivePanelClasses;
                    registerPanel.className = activePanelClasses;
                    registerBtn.className = 'py-2.5 rounded-xl transition cursor-pointer bg-sage-500 text-white shadow-xs';
                    loginBtn.className = 'py-2.5 rounded-xl transition cursor-pointer text-stone-600 hover:text-sage-600';
                    if (modalTitle) {
                        modalTitle.textContent = isEn ? 'Create New Account' : 'Đăng Ký Tài Khoản Mới';
                    }
                } else {
                    registerPanel.className = inactivePanelClasses;
                    loginPanel.className = activePanelClasses;
                    loginBtn.className = 'py-2.5 rounded-xl transition cursor-pointer bg-sage-500 text-white shadow-xs';
                    registerBtn.className = 'py-2.5 rounded-xl transition cursor-pointer text-stone-600 hover:text-sage-600';
                    if (modalTitle) {
                        modalTitle.textContent = isEn ? 'Customer Login' : 'Đăng Nhập Khách Hàng';
                    }
                }
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeAuthModal();
                }
            });
        </script>
    @endif

    <!-- Footer -->
    <footer class="bg-stone-900 text-stone-300 mt-20 border-t border-sage-200/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 md:grid-cols-4 gap-10">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo-footer.png') }}?v=5" alt="SHINE Beauty Logo" class="w-14 h-14 object-contain drop-shadow-sm">
                    <span class="font-serif text-2xl font-bold text-white">
                        SHINE <span class="text-sage-400 italic font-normal">Beauty</span>
                    </span>
                </div>
                <p class="text-xs text-stone-400 leading-relaxed">
                    Hệ thống phân phối mỹ phẩm cao cấp & chăm sóc da chuẩn khoa học chính hãng từ Pháp, Hàn Quốc và Nhật Bản.
                </p>
            </div>
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest text-sage-400 mb-3">Danh Mục Làm Đẹp</h4>
                <ul class="space-y-2 text-xs text-stone-400">
                    <li><a href="{{ route('products.index', ['category' => 'cham-soc-da']) }}" class="hover:text-white">Chăm sóc da chuyên sâu</a></li>
                    <li><a href="{{ route('products.index', ['category' => 'trang-diem']) }}" class="hover:text-white">Trang điểm cao cấp</a></li>
                    <li><a href="{{ route('products.index', ['category' => 'cham-soc-co-the']) }}" class="hover:text-white">Chăm sóc cơ thể</a></li>
                    <li><a href="{{ route('products.index', ['category' => 'nuoc-hoa']) }}" class="hover:text-white">Nước hoa chính hãng</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white">Blog mỹ phẩm &amp; chăm sóc da</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white">Về Chúng Tôi</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest text-sage-400 mb-3">Theo Loại Da</h4>
                <ul class="space-y-2 text-xs text-stone-400">
                    <li><a href="{{ route('products.index', ['skin_type' => 'Da dầu']) }}" class="hover:text-white">Da dầu & Lỗ chân lông to</a></li>
                    <li><a href="{{ route('products.index', ['skin_type' => 'Da khô']) }}" class="hover:text-white">Da khô & Thiếu ẩm</a></li>
                    <li><a href="{{ route('products.index', ['skin_type' => 'Da nhạy cảm']) }}" class="hover:text-white">Da nhạy cảm & Phục hồi B5</a></li>
                    <li><a href="{{ route('products.index', ['skin_type' => 'Mọi loại da']) }}" class="hover:text-white">Mọi loại da</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest text-sage-400 mb-3">Liên Hệ & Hỗ Trợ</h4>
                <p class="text-xs text-stone-400 leading-relaxed">
                    Hotline tư vấn da liễu: 1900 6868<br>
                    Cửa Hàng: Đường Phạm Văn Chiêu, Phường Thông Tây Hội, TP Hồ Chí Minh<br>
                    Chúng tôi cam kết cung cấp sản phẩm chính hãng 100%, đi kèm tem phụ tiếng Việt theo đúng quy định.
                </p>
            </div>
        </div>
    </footer>

    <div id="wishlist-toast" class="fixed bottom-5 right-5 z-50 hidden bg-stone-900 text-white text-xs font-medium px-4 py-3 rounded-2xl shadow-xl border border-stone-700 flex items-center gap-2.5 transition-all duration-300">
        <span id="wishlist-toast-text"></span>
        <a href="{{ route('customer.wishlist') }}" class="text-red-400 hover:underline font-bold ml-2 flex-shrink-0">Xem Yêu Thích →</a>
    </div>

    <script>
        const IS_CUSTOMER_LOGGED_IN = {{ auth()->check() ? 'true' : 'false' }};
        let wishlistToastTimeout = null;

        function showWishlistToast(message) {
            const toast = document.getElementById('wishlist-toast');
            const textEl = document.getElementById('wishlist-toast-text');
            if (!toast || !textEl) return;
            textEl.textContent = message;
            toast.classList.remove('hidden');
            if (wishlistToastTimeout) clearTimeout(wishlistToastTimeout);
            wishlistToastTimeout = setTimeout(() => {
                toast.classList.add('hidden');
            }, 3200);
        }

        async function toggleWishlistProduct(productId, btn) {
            if (!IS_CUSTOMER_LOGGED_IN) {
                if (typeof openAuthModal === 'function') {
                    openAuthModal('login');
                } else {
                    window.location.href = "{{ route('customer.login') }}";
                }
                return;
            }

            try {
                const res = await fetch("{{ route('wishlist.toggle') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ product_id: productId })
                });

                if (res.status === 401) {
                    if (typeof openAuthModal === 'function') {
                        openAuthModal('login');
                    } else {
                        window.location.href = "{{ route('customer.login') }}";
                    }
                    return;
                }

                const data = await res.json();
                if (data.status !== 'ok') return;

                const isFav = Boolean(data.favorited);

                // Cập nhật toàn bộ nút trái tim của sản phẩm này trên trang
                document.querySelectorAll(`[data-wishlist-btn="${productId}"]`).forEach(el => {
                    el.setAttribute('data-favorited', isFav ? '1' : '0');
                    el.title = isFav ? 'Bỏ yêu thích sản phẩm này' : 'Thêm vào danh mục Yêu Thích';

                    const activeClasses = ['opacity-100', 'bg-red-50', 'text-red-500', 'border-red-300', 'shadow-md', 'shadow-red-200/80', 'ring-2', 'ring-red-400/50', 'scale-105'];
                    const inactiveClasses = ['opacity-40', 'hover:opacity-95', 'bg-white/80', 'bg-white', 'text-stone-400', 'hover:text-red-500', 'border-stone-200/80', 'border-stone-200'];

                    if (isFav) {
                        el.classList.remove(...inactiveClasses);
                        el.classList.add(...activeClasses);
                    } else {
                        el.classList.remove(...activeClasses);
                        el.classList.add('opacity-40', 'hover:opacity-95', 'bg-white/80', 'text-stone-400', 'hover:text-red-500', 'border-stone-200/80');
                    }

                    const svg = el.querySelector('svg');
                    if (svg) {
                        svg.setAttribute('fill', isFav ? 'currentColor' : 'none');
                    }
                });

                // Nếu đang ở trang Danh mục Yêu Thích và bỏ yêu thích thì ẩn thẻ sản phẩm
                if (!isFav) {
                    const wishlistItemCard = document.getElementById(`wishlist-item-card-${productId}`);
                    if (wishlistItemCard) {
                        wishlistItemCard.remove();
                    }
                }

                // Cập nhật bộ đếm số lượng Yêu Thích
                document.querySelectorAll('[data-wishlist-count]').forEach(badge => {
                    badge.textContent = data.count;
                });

                showWishlistToast(data.message);
            } catch (e) {
                console.error(e);
            }
        }
    </script>

    @include('partials.i18n-en')
</body>
</html>
