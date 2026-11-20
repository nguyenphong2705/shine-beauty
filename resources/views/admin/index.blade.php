<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị Viên Shop (Admin) | SHINE Beauty</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=5">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}?v=5">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,400&display=swap" rel="stylesheet">
    <script>
        const sageMintPalette = {
            50: '#F2F9F6',
            100: '#DFF2EA',
            200: '#BFE4D5',
            300: '#94CEB8',
            400: '#68B398',
            500: '#4A947A',
            600: '#397A63',
            700: '#2F6250',
            800: '#274F42',
            900: '#214237',
            950: '#11251E',
        };
        const terracottaPalette = {
            50: '#FDF5F2',
            100: '#FBE8E1',
            200: '#F6D0C2',
            300: '#EEB09A',
            400: '#E28768',
            500: '#D46542',
            600: '#C04E2C',
            700: '#9E3E22',
            800: '#82341F',
            900: '#6B2D1D',
            950: '#3A150C',
        };
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Roboto', 'sans-serif'],
                        serif: ['Roboto', 'sans-serif'],
                    },
                    colors: {
                        sage: sageMintPalette,
                        rose: sageMintPalette,
                        mint: {
                            50: '#F0FDF7',
                            100: '#DCFCEB',
                            200: '#BBF7D8',
                            300: '#86EFBA',
                            400: '#4ADE96',
                            500: '#2AB876',
                            600: '#1D965E',
                            700: '#19774C',
                            800: '#185E3F',
                            900: '#154E35',
                        },
                        terracotta: terracottaPalette,
                        wine: terracottaPalette
                    }
                }
            }
        }
    </script>
    <style>
        body, h1, h2, h3, h4, h5, h6, input, button, select, textarea {
            font-family: 'Roboto', sans-serif;
        }
        html, body {
            overflow-x: clip;
            max-width: 100%;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .admin-sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .admin-sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .admin-sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(168, 162, 158, 0.25);
            border-radius: 9999px;
        }
        .admin-sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(168, 162, 158, 0.45);
        }
        .admin-content-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .admin-content-scroll::-webkit-scrollbar-track {
            background: #f5f5f4;
            border-radius: 9999px;
        }
        .admin-content-scroll::-webkit-scrollbar-thumb {
            background: #d6d3d1;
            border-radius: 9999px;
        }
        .admin-content-scroll::-webkit-scrollbar-thumb:hover {
            background: #a8a29e;
        }
        #desc-editor:empty:before {
            content: attr(data-placeholder);
            color: #a8a29e;
            pointer-events: none;
        }
        #desc-editor h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #292524;
            margin: 0.6rem 0 0.35rem;
        }
        #desc-editor h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #397A63;
            margin: 0.5rem 0 0.25rem;
        }
        #desc-editor ul {
            list-style-type: disc;
            padding-left: 1.25rem;
            margin: 0.4rem 0;
        }
        #desc-editor ol {
            list-style-type: decimal;
            padding-left: 1.25rem;
            margin: 0.4rem 0;
        }
        #desc-editor img {
            max-width: 100%;
            height: auto;
            border-radius: 0.875rem;
            margin: 0.65rem 0;
            border: 1px solid #DFF2EA;
        }
    </style>
</head>
<body class="font-sans antialiased bg-stone-100 text-stone-800 min-h-screen flex flex-col">
    <!-- Top Admin Header -->
    <header class="sticky top-0 z-40 bg-stone-900 text-white border-b border-stone-800 shadow-md">
        <div class="w-full px-4 sm:px-6 min-h-[64px] lg:h-16 py-2 lg:py-0 flex flex-wrap lg:flex-nowrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                <button type="button"
                        onclick="toggleAdminMobileSidebar()"
                        class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-xl bg-stone-800 hover:bg-stone-700 text-sage-300 border border-stone-700 flex-shrink-0 cursor-pointer"
                        aria-label="Mở menu quản trị">
                    ☰
                </button>
                <a href="{{ route('admin.index') }}" class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <img src="{{ asset('logo-footer.png') }}?v=5" alt="SHINE Beauty Logo" width="48" height="48" class="w-9 h-9 sm:w-11 sm:h-11 object-contain drop-shadow-sm flex-shrink-0">
                    <div class="min-w-0">
                        <span class="font-serif text-base sm:text-lg font-bold tracking-tight text-white truncate block sm:inline">
                            Shine <span class="text-sage-400 font-normal italic">Admin</span>
                        </span>
                        <span class="hidden md:inline-block ml-2 text-[10px] uppercase tracking-widest bg-sage-500/20 text-sage-300 border border-sage-500/30 px-2 py-0.5 rounded-full">
                            Quản Trị Viên Của Shop
                        </span>
                    </div>
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2.5">
                <a href="{{ route('customer.login') }}" target="_blank" class="text-[11px] sm:text-xs font-medium bg-stone-800 hover:bg-stone-700 text-sage-300 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl border border-stone-700 transition whitespace-nowrap">
                    👤 <span class="hidden sm:inline">Trang Khách Hàng</span><span class="sm:hidden">Khách</span> ↗
                </a>
                <a href="{{ route('home') }}" target="_blank" class="text-[11px] sm:text-xs font-medium bg-stone-800 hover:bg-stone-700 text-stone-200 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl border border-stone-700 transition whitespace-nowrap">
                    🌐 <span class="hidden sm:inline">Cửa Hàng</span><span class="sm:hidden">Shop</span> ↗
                </a>

                <div class="hidden xl:flex items-center gap-2 pl-2 border-l border-stone-700">
                    <div class="w-8 h-8 rounded-full bg-sage-500/20 border border-sage-400/40 text-sage-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="text-xs leading-tight max-w-[160px]">
                        <p class="font-semibold text-white truncate">{{ auth()->user()->name ?? 'Quản trị viên' }}</p>
                        <p class="text-[11px] text-stone-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                </div>

                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-[11px] sm:text-xs font-semibold bg-red-500/15 hover:bg-red-500/25 text-red-300 border border-red-500/30 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl transition whitespace-nowrap cursor-pointer">
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>

        <!-- Mobile & Tablet Quick Navigation Bar (< lg) -->
        <div class="lg:hidden bg-stone-950/90 border-t border-stone-800 px-3 py-2 flex items-center gap-1.5 overflow-x-auto no-scrollbar text-xs font-semibold">
            <a href="{{ route('admin.analytics') }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition {{ $activePage === 'analytics' ? 'bg-sage-500 text-white shadow-sm' : 'bg-stone-800/80 text-stone-300' }}">
                📊 Thống Kê ({{ $stats['total_products'] }})
            </a>
            <a href="{{ route('admin.orders.index') }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition {{ $activePage === 'orders' ? 'bg-sage-500 text-white shadow-sm' : 'bg-stone-800/80 text-stone-300' }}">
                📦 Đơn Hàng ({{ $allOrders->count() }})
            </a>
            <a href="{{ route('admin.products.index') }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition {{ $activePage === 'products' ? 'bg-sage-500 text-white shadow-sm' : 'bg-stone-800/80 text-stone-300' }}">
                💄 Sản Phẩm ({{ $allProducts->count() }})
            </a>
            <a href="{{ route('admin.coupons.index') }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition {{ $activePage === 'coupons' ? 'bg-sage-500 text-white shadow-sm' : 'bg-stone-800/80 text-stone-300' }}">
                🎟️ Voucher ({{ $coupons->count() }})
            </a>
            <a href="{{ route('admin.payments.index') }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition {{ $activePage === 'payments' ? 'bg-sage-500 text-white shadow-sm' : 'bg-stone-800/80 text-stone-300' }}">
                🏦 Đối Soát SePay ({{ isset($paymentTransactions) ? $paymentTransactions->count() : 0 }})
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition {{ $activePage === 'reviews' ? 'bg-sage-500 text-white shadow-sm' : 'bg-stone-800/80 text-stone-300' }}">
                💬 Bình Luận ({{ isset($allReviews) ? $allReviews->count() : 0 }})
            </a>
        </div>
    </header>

    <div class="flex-1 flex flex-col lg:flex-row w-full max-w-full min-w-0">
        <!-- Mobile Sidebar Backdrop -->
        <div id="admin-sidebar-backdrop" onclick="toggleAdminMobileSidebar(false)" class="hidden fixed inset-0 z-40 bg-stone-950/60 backdrop-blur-sm lg:hidden"></div>

        <!-- Left Sidebar: Danh Mục Quản Trị & Danh Mục Mỹ Phẩm -->
        <aside id="admin-mobile-sidebar" class="hidden lg:block fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] h-screen lg:sticky lg:top-16 lg:inset-auto lg:z-auto lg:w-64 xl:w-[272px] 2xl:w-72 lg:h-[calc(100vh-4rem)] flex-shrink-0 bg-stone-900 text-stone-200 border-r border-stone-800 overflow-y-auto admin-sidebar-scroll p-4 space-y-5 shadow-2xl lg:shadow-none">
            <!-- Mobile Drawer Header -->
            <div class="flex lg:hidden items-center justify-between pb-3 border-b border-stone-800">
                <span class="text-xs font-bold uppercase tracking-widest text-sage-400">Danh Mục Quản Trị</span>
                <button type="button" onclick="toggleAdminMobileSidebar(false)" class="w-8 h-8 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 flex items-center justify-center text-sm cursor-pointer" aria-label="Đóng menu">
                    ✕
                </button>
            </div>

            <!-- 1. Danh Mục Quản Trị Hệ Thống -->
            <div class="space-y-2">
                <p class="px-2.5 text-[10px] font-bold uppercase tracking-widest text-sage-400">
                    Danh Mục Quản Trị
                </p>
                <nav class="space-y-1 text-xs font-semibold">
                    <a href="{{ route('admin.analytics') }}" class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl transition {{ $activePage === 'analytics' ? 'bg-sage-500 text-white shadow-sm' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}">
                        <span class="flex items-center gap-2.5 min-w-0">
                            <span class="flex-shrink-0">📊</span>
                            <span class="truncate">1. Thống Kê & Tồn Kho</span>
                        </span>
                        <span class="flex-shrink-0 min-w-[26px] text-center text-[10px] font-bold tabular-nums px-1.5 py-0.5 rounded-full {{ $activePage === 'analytics' ? 'bg-white/20 text-white' : 'bg-stone-800 text-sage-300' }}">{{ $stats['total_products'] }}</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl transition {{ $activePage === 'orders' ? 'bg-sage-500 text-white shadow-sm' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}">
                        <span class="flex items-center gap-2.5 min-w-0">
                            <span class="flex-shrink-0">📦</span>
                            <span class="truncate">2. Tra Cứu & Duyệt Đơn</span>
                        </span>
                        <span class="flex-shrink-0 min-w-[26px] text-center text-[10px] font-bold tabular-nums px-1.5 py-0.5 rounded-full {{ $activePage === 'orders' ? 'bg-white/20 text-white' : 'bg-stone-800 text-sage-300' }}">{{ $allOrders->count() }}</span>
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl transition {{ $activePage === 'products' ? 'bg-sage-500 text-white shadow-sm' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}">
                        <span class="flex items-center gap-2.5 min-w-0">
                            <span class="flex-shrink-0">💄</span>
                            <span class="truncate">3. Quản Lý Sản Phẩm</span>
                        </span>
                        <span class="flex-shrink-0 min-w-[26px] text-center text-[10px] font-bold tabular-nums px-1.5 py-0.5 rounded-full {{ $activePage === 'products' ? 'bg-white/20 text-white' : 'bg-stone-800 text-sage-300' }}">{{ $allProducts->count() }}</span>
                    </a>
                    <a href="{{ route('admin.coupons.index') }}" class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl transition {{ $activePage === 'coupons' ? 'bg-sage-500 text-white shadow-sm' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}">
                        <span class="flex items-center gap-2.5 min-w-0">
                            <span class="flex-shrink-0">🎟️</span>
                            <span class="truncate">4. Quản Lý Voucher</span>
                        </span>
                        <span class="flex-shrink-0 min-w-[26px] text-center text-[10px] font-bold tabular-nums px-1.5 py-0.5 rounded-full {{ $activePage === 'coupons' ? 'bg-white/20 text-white' : 'bg-stone-800 text-sage-300' }}">{{ $coupons->count() }}</span>
                    </a>
                    <a href="{{ route('admin.payments.index') }}" title="5. Giao Dịch & Đối Soát SePay" class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl transition {{ $activePage === 'payments' ? 'bg-sage-500 text-white shadow-sm' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}">
                        <span class="flex items-center gap-2.5 min-w-0">
                            <span class="flex-shrink-0">🏦</span>
                            <span class="truncate">5. Đối Soát SePay</span>
                        </span>
                        <span class="flex-shrink-0 min-w-[26px] text-center text-[10px] font-bold tabular-nums px-1.5 py-0.5 rounded-full {{ $activePage === 'payments' ? 'bg-white/20 text-white' : 'bg-stone-800 text-sage-300' }}">{{ isset($paymentTransactions) ? $paymentTransactions->count() : 0 }}</span>
                    </a>
                    <a href="{{ route('admin.reviews.index') }}" title="6. Quản Lý Bình Luận & Đánh Giá" class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl transition {{ $activePage === 'reviews' ? 'bg-sage-500 text-white shadow-sm' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}">
                        <span class="flex items-center gap-2.5 min-w-0">
                            <span class="flex-shrink-0">💬</span>
                            <span class="truncate">6. Quản Lý Bình Luận</span>
                        </span>
                        <span class="flex-shrink-0 min-w-[26px] text-center text-[10px] font-bold tabular-nums px-1.5 py-0.5 rounded-full {{ $activePage === 'reviews' ? 'bg-white/20 text-white' : 'bg-stone-800 text-sage-300' }}">{{ isset($allReviews) ? $allReviews->count() : 0 }}</span>
                    </a>
                </nav>
            </div>

            <!-- 2. Danh Mục Mỹ Phẩm (Lọc & Thêm nhanh bên góc trái) -->
            <div class="pt-4 border-t border-stone-800 space-y-2.5">
                <div class="flex items-center justify-between px-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-sage-400">
                        Danh Mục Mỹ Phẩm ({{ $categories->count() }})
                    </p>
                    @if($categoryFilter !== 'ALL')
                        <a href="{{ route('admin.products.index') }}" class="text-[10px] font-semibold text-sage-300 hover:underline whitespace-nowrap">Bỏ lọc</a>
                    @endif
                </div>

                <div class="space-y-1 text-xs max-h-52 overflow-y-auto admin-sidebar-scroll pr-0.5">
                    <a href="{{ route('admin.products.index', ['category_filter' => 'ALL', 'stock_filter' => $stockFilter]) }}"
                       class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl transition {{ $activePage === 'products' && $categoryFilter === 'ALL' ? 'bg-stone-800 text-sage-300 font-bold border border-sage-500/40' : 'text-stone-400 hover:bg-stone-800/60 hover:text-white border border-transparent' }}">
                        <span class="truncate">✦ Tất cả danh mục</span>
                        <span class="flex-shrink-0 text-[10px] font-semibold tabular-nums px-2 py-0.5 rounded-full {{ $activePage === 'products' && $categoryFilter === 'ALL' ? 'bg-sage-500/20 text-sage-200' : 'bg-stone-800/90 text-stone-400' }}">{{ $allProducts->count() }} SP</span>
                    </a>
                    @foreach($categories as $c)
                        @php $isCatActive = $activePage === 'products' && (string)$categoryFilter === (string)$c->id; @endphp
                        <a href="{{ route('admin.products.index', ['category_filter' => $c->id, 'stock_filter' => $stockFilter]) }}"
                           class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl transition {{ $isCatActive ? 'bg-stone-800 text-sage-300 font-bold border border-sage-500/40' : 'text-stone-400 hover:bg-stone-800/60 hover:text-white border border-transparent' }}">
                            <span class="truncate">{{ $c->name }}</span>
                            <span class="flex-shrink-0 text-[10px] font-semibold tabular-nums px-2 py-0.5 rounded-full {{ $isCatActive ? 'bg-sage-500/20 text-sage-200' : 'bg-stone-800/90 text-stone-400' }}">{{ $c->products_count ?? $allProducts->where('category_id', $c->id)->count() }} SP</span>
                        </a>
                    @endforeach
                </div>

                <!-- Form thêm nhanh Danh mục ở cột trái -->
                <form action="{{ route('admin.categories.store') }}" method="POST" class="pt-2">
                    @csrf
                    <div class="flex items-center gap-1.5">
                        <input type="text" name="name" required placeholder="Thêm danh mục mới..."
                            class="min-w-0 flex-1 px-3 py-1.5 text-xs rounded-xl bg-stone-800 border border-stone-700 text-white placeholder-stone-500 focus:outline-none focus:border-sage-400">
                        <button type="submit" title="Thêm danh mục mới" class="w-9 h-8 flex items-center justify-center rounded-xl bg-sage-500 hover:bg-sage-600 text-white text-sm font-bold flex-shrink-0 transition cursor-pointer">
                            +
                        </button>
                    </div>
                </form>
            </div>

            <!-- 3. Lọc Nhanh Theo Tình Trạng Kho -->
            @php $inStockCount = $allProducts->where('stock', '>', 15)->count(); @endphp
            <div class="pt-4 border-t border-stone-800 space-y-2.5">
                <p class="px-2.5 text-[10px] font-bold uppercase tracking-widest text-sage-400">
                    Lọc Nhanh Kho Hàng
                </p>
                <div class="grid grid-cols-2 gap-2 text-[11px]">
                    <a href="{{ route('admin.products.index', ['stock_filter' => 'BEST_SELLER']) }}"
                       class="flex flex-col gap-0.5 px-2.5 py-2 rounded-xl border transition {{ $activePage === 'products' && $stockFilter === 'BEST_SELLER' ? 'bg-amber-500/20 border-amber-400/60 text-amber-200' : 'bg-stone-800/80 hover:bg-stone-800 border-stone-700/80 text-amber-300' }}">
                        <span class="font-medium truncate">★ Bán chạy</span>
                        <span class="text-xs font-bold tabular-nums">{{ $stats['best_seller_count'] }} SP</span>
                    </a>
                    <a href="{{ route('admin.products.index', ['stock_filter' => 'OUT_OF_STOCK']) }}"
                       class="flex flex-col gap-0.5 px-2.5 py-2 rounded-xl border transition {{ $activePage === 'products' && $stockFilter === 'OUT_OF_STOCK' ? 'bg-red-500/20 border-red-400/60 text-red-200' : 'bg-stone-800/80 hover:bg-stone-800 border-stone-700/80 text-red-400' }}">
                        <span class="font-medium truncate">🔴 Hết hàng</span>
                        <span class="text-xs font-bold tabular-nums">{{ $stats['out_of_stock_count'] }} SP</span>
                    </a>
                    <a href="{{ route('admin.products.index', ['stock_filter' => 'LOW_STOCK']) }}"
                       class="flex flex-col gap-0.5 px-2.5 py-2 rounded-xl border transition {{ $activePage === 'products' && $stockFilter === 'LOW_STOCK' ? 'bg-orange-500/20 border-orange-400/60 text-orange-200' : 'bg-stone-800/80 hover:bg-stone-800 border-stone-700/80 text-orange-300' }}">
                        <span class="font-medium truncate">🟠 Sắp hết</span>
                        <span class="text-xs font-bold tabular-nums">{{ $stats['low_stock_count'] }} SP</span>
                    </a>
                    <a href="{{ route('admin.products.index', ['stock_filter' => 'IN_STOCK']) }}"
                       class="flex flex-col gap-0.5 px-2.5 py-2 rounded-xl border transition {{ $activePage === 'products' && $stockFilter === 'IN_STOCK' ? 'bg-emerald-500/20 border-emerald-400/60 text-emerald-200' : 'bg-stone-800/80 hover:bg-stone-800 border-stone-700/80 text-emerald-300' }}">
                        <span class="font-medium truncate">🟢 Còn hàng</span>
                        <span class="text-xs font-bold tabular-nums">{{ $inStockCount }} SP</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Right Main Content Area -->
        <main class="flex-1 min-w-0 w-full px-4 sm:px-6 lg:px-7 py-5 sm:py-6">
        <div class="w-full max-w-[1440px] mx-auto space-y-6">
        <!-- Flash Notifications -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 sm:px-5 py-3.5 rounded-2xl text-xs sm:text-sm font-medium flex items-center justify-between shadow-sm break-words">
                <span>✓ {{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 sm:px-5 py-3.5 rounded-2xl text-xs sm:text-sm font-medium shadow-sm break-words">
                ⚠ {{ $errors->first() }}
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- 6 KPI SUMMARY CARDS: DOANH THU, ĐƠN HÀNG, BÁN CHẠY, HẾT HÀNG, SẮP HẾT, VOUCHER -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-2 sm:grid-cols-3 2xl:grid-cols-6 gap-3 sm:gap-4">
            <a href="{{ route('admin.analytics') }}" class="min-w-0 flex flex-col justify-between bg-white p-4 rounded-2xl border border-stone-200/80 shadow-sm hover:shadow hover:border-sage-300 transition">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-stone-400 block truncate">Tổng Doanh Thu</span>
                <p title="{{ number_format($stats['total_revenue'], 0, ',', '.') }}₫" class="text-base sm:text-xl font-extrabold tabular-nums text-sage-600 truncate mt-1.5">{{ number_format($stats['total_revenue'], 0, ',', '.') }}₫</p>
                <p title="Thực thu: {{ number_format($stats['completed_revenue'], 0, ',', '.') }}₫" class="text-[11px] text-emerald-600 font-medium truncate mt-1">Thực thu: {{ number_format($stats['completed_revenue'], 0, ',', '.') }}₫</p>
            </a>

            <a href="{{ route('admin.orders.index') }}" class="min-w-0 flex flex-col justify-between bg-white p-4 rounded-2xl border border-stone-200/80 shadow-sm hover:shadow hover:border-sage-300 transition">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-stone-400 block truncate">Tổng Đơn Hàng</span>
                <p title="{{ $stats['total_orders'] }} đơn" class="text-base sm:text-xl font-extrabold tabular-nums text-stone-800 truncate mt-1.5">{{ $stats['total_orders'] }} đơn</p>
                <p title="{{ $stats['pending_orders'] }} đơn đang xử lý" class="text-[11px] text-amber-600 font-medium truncate mt-1">{{ $stats['pending_orders'] }} đơn đang xử lý</p>
            </a>

            <a href="{{ route('admin.products.index', ['stock_filter' => 'BEST_SELLER']) }}" class="min-w-0 flex flex-col justify-between bg-amber-50/50 p-4 rounded-2xl border border-amber-200 shadow-sm hover:shadow hover:border-amber-400 transition">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-amber-700 block truncate">★ SP Bán Chạy</span>
                <p title="{{ $stats['best_seller_count'] }} SP" class="text-base sm:text-xl font-extrabold tabular-nums text-amber-600 truncate mt-1.5">{{ $stats['best_seller_count'] }} SP</p>
                <p class="text-[11px] text-stone-500 truncate mt-1">Top doanh số cao nhất</p>
            </a>

            <a href="{{ route('admin.products.index', ['stock_filter' => 'OUT_OF_STOCK']) }}" class="min-w-0 flex flex-col justify-between bg-red-50/50 p-4 rounded-2xl border border-red-200 shadow-sm hover:shadow hover:border-red-400 transition">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-red-600 block truncate">🔴 SP Hết Hàng (0)</span>
                <p title="{{ $stats['out_of_stock_count'] }} SP" class="text-base sm:text-xl font-extrabold tabular-nums text-red-600 truncate mt-1.5">{{ $stats['out_of_stock_count'] }} SP</p>
                <p class="text-[11px] text-red-500 font-medium truncate mt-1">Cần nhập thêm ngay</p>
            </a>

            <a href="{{ route('admin.products.index', ['stock_filter' => 'LOW_STOCK']) }}" class="min-w-0 flex flex-col justify-between bg-orange-50/50 p-4 rounded-2xl border border-orange-200 shadow-sm hover:shadow hover:border-orange-400 transition">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-orange-600 block truncate">🟠 Sắp Hết (≤15)</span>
                <p title="{{ $stats['low_stock_count'] }} SP" class="text-base sm:text-xl font-extrabold tabular-nums text-orange-600 truncate mt-1.5">{{ $stats['low_stock_count'] }} SP</p>
                <p class="text-[11px] text-stone-500 truncate mt-1">Tổng kho: {{ $stats['total_products'] }} SP</p>
            </a>

            <a href="{{ route('admin.coupons.index') }}" class="min-w-0 flex flex-col justify-between bg-white p-4 rounded-2xl border border-stone-200/80 shadow-sm hover:shadow hover:border-sage-300 transition">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-stone-400 block truncate">Voucher & Khách</span>
                <p title="{{ $stats['active_coupons'] }} Voucher" class="text-base sm:text-xl font-extrabold tabular-nums text-emerald-600 truncate mt-1.5">{{ $stats['active_coupons'] }} Voucher</p>
                <p class="text-[11px] text-stone-500 truncate mt-1">{{ $stats['customers_count'] }} TK khách hàng</p>
            </a>
        </div>

        @if($activePage === 'analytics')
        <!-- ========================================================================= -->
        <!-- SECTION 1: THỐNG KÊ RÕ RÀNG SẢN PHẨM BÁN CHẠY, HẾT HÀNG & SẮP HẾT HÀNG -->
        <!-- ========================================================================= -->
        <section id="section-analytics" class="grid grid-cols-1 xl:grid-cols-12 gap-5 lg:gap-6 items-stretch">
            <!-- Left: Top Selling / Best Seller Products -->
            <div class="xl:col-span-6 flex flex-col min-w-0 bg-white rounded-3xl border border-stone-200/80 p-5 sm:p-6 shadow-sm space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-100 pb-3.5">
                    <div class="min-w-0">
                        <span class="text-[11px] font-bold uppercase tracking-widest text-amber-600">★ Thống Kê Doanh Số Sản Phẩm</span>
                        <h2 class="font-serif text-lg sm:text-xl font-bold text-stone-800">
                            Sản Phẩm Bán Chạy Nhất ({{ $bestSellingProducts->count() }})
                        </h2>
                    </div>
                    <a href="{{ route('admin.products.index', ['stock_filter' => 'BEST_SELLER']) }}" class="text-xs font-semibold text-sage-600 hover:underline whitespace-nowrap">
                        Lọc trong kho →
                    </a>
                </div>

                <div class="flex-1 space-y-2.5 max-h-[460px] overflow-y-auto admin-content-scroll pr-1">
                    @foreach($bestSellingProducts as $idx => $prod)
                        <div class="flex flex-wrap sm:flex-nowrap items-center justify-between gap-3 p-3 rounded-2xl border border-amber-100 bg-amber-50/25">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <span class="w-7 h-7 rounded-xl bg-amber-500 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    #{{ $idx + 1 }}
                                </span>
                                <img src="{{ $prod->first_image }}" alt="{{ $prod->name }}" class="w-11 h-11 rounded-xl object-cover bg-white border border-stone-200 flex-shrink-0">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="font-mono font-bold text-[10px] bg-stone-900 text-white px-2 py-0.5 rounded-md flex-shrink-0">{{ $prod->product_code }}</span>
                                        <a href="{{ route('products.show', $prod->slug) }}" target="_blank" class="text-xs font-bold text-stone-800 hover:text-sage-600 line-clamp-1">
                                            {{ $prod->name }}
                                        </a>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-stone-500 mt-0.5">
                                        <span>Đã bán: <strong class="text-emerald-600">{{ $prod->sold_quantity }} SP</strong></span>
                                        <span>• Doanh thu: <strong class="text-sage-600">{{ number_format($prod->sold_revenue, 0, ',', '.') }}₫</strong></span>
                                        <span>• Tồn kho: <strong class="text-stone-800">{{ $prod->stock }} SP</strong></span>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('admin.products.best-seller', $prod) }}" method="POST" class="flex-shrink-0 ml-auto">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-[11px] font-bold px-2.5 py-1 rounded-xl border transition whitespace-nowrap cursor-pointer {{ $prod->is_best_seller ? 'bg-[#D4728C] text-white border-[#C25E79]' : 'bg-stone-100 text-stone-500 border-stone-200 hover:bg-stone-200' }}">
                                    {{ $prod->is_best_seller ? '✦ Best Seller' : 'Đặt Best Seller' }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right: Inventory Status, Standardization & Quick Stock Adjustment -->
            <div class="xl:col-span-6 flex flex-col min-w-0 bg-white rounded-3xl border border-stone-200/80 p-5 sm:p-6 shadow-sm space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-100 pb-3.5">
                    <div class="min-w-0">
                        <span class="text-[11px] font-bold uppercase tracking-widest text-emerald-600">📦 Kiểm Soát Tồn Kho & Mã SP</span>
                        <div class="flex flex-wrap items-center gap-2 mt-0.5">
                            <h2 class="font-serif text-lg sm:text-xl font-bold text-stone-800">Kiểm Soát Tồn Kho</h2>
                            <div class="flex flex-wrap items-center gap-1 text-[11px] font-semibold">
                                <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200">Hết: {{ $outOfStockProducts->count() }}</span>
                                <span class="px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 border border-orange-200">Sắp hết: {{ $lowStockProducts->count() }}</span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Đủ kho: {{ $allProducts->where('stock', '>', 15)->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('admin.products.standardize') }}" method="POST" class="flex-shrink-0"
                          data-confirm="true"
                          data-confirm-type="warning"
                          data-confirm-title="Chuẩn hóa SKU & tồn kho"
                          data-confirm-message="Hệ thống sẽ chuẩn hóa lại toàn bộ mã SKU (LMN-SP...) và đặt tồn kho tiêu chuẩn (20 SP/mã) cho các sản phẩm."
                          data-confirm-btn="Chuẩn hóa ngay">
                        @csrf
                        <button type="submit" class="text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-xl shadow-sm transition whitespace-nowrap cursor-pointer">
                            🔄 Chuẩn hóa SKU & Kho (20)
                        </button>
                    </form>
                </div>

                @if($outOfStockProducts->isEmpty() && $lowStockProducts->isEmpty())
                    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5 text-xs text-emerald-800 space-y-0.5">
                        <p class="font-bold">✓ Toàn bộ {{ $allProducts->count() }} sản phẩm đã chuẩn hóa đồng bộ!</p>
                        <p>Mã sản phẩm <strong>LMN-SP001 → LMN-SP{{ str_pad((string)$allProducts->count(), 3, '0', STR_PAD_LEFT) }}</strong> • Tồn kho tiêu chuẩn: <strong>20 SP/mã</strong>.</p>
                    </div>
                @endif

                <div class="flex-1 space-y-2.5 max-h-[460px] overflow-y-auto admin-content-scroll pr-1">
                    @foreach(($outOfStockProducts->count() > 0 || $lowStockProducts->count() > 0) ? $outOfStockProducts->concat($lowStockProducts) : $allProducts as $prod)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-2xl border {{ $prod->stock <= 0 ? 'border-red-200 bg-red-50/40' : ($prod->stock <= 15 ? 'border-orange-200 bg-orange-50/40' : 'border-stone-200/80 bg-stone-50/60') }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $prod->first_image }}" alt="{{ $prod->name }}" class="w-11 h-11 rounded-xl object-cover bg-white border border-stone-200 flex-shrink-0">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="font-mono font-bold text-[10px] bg-stone-900 text-white px-2 py-0.5 rounded-md">
                                            {{ $prod->product_code }}
                                        </span>
                                        @if($prod->stock <= 0)
                                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-red-600 text-white">HẾT HÀNG (0)</span>
                                        @elseif($prod->stock <= 15)
                                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-orange-500 text-white">SẮP HẾT ({{ $prod->stock }})</span>
                                        @else
                                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">TỒN KHO: {{ $prod->stock }} SP</span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-bold text-stone-800 line-clamp-1 mt-0.5">{{ $prod->name }}</p>
                                </div>
                            </div>

                            <form action="{{ route('admin.products.stock', $prod) }}" method="POST" class="flex items-center gap-1.5 flex-shrink-0">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="stock" value="{{ $prod->stock }}" min="0" class="w-16 px-2 py-1 text-xs font-bold text-center rounded-xl border border-stone-300 bg-white">
                                <button type="submit" class="bg-stone-900 hover:bg-sage-600 text-white text-[11px] font-semibold px-2.5 py-1 rounded-xl transition cursor-pointer whitespace-nowrap">
                                    Lưu kho
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif


        @if($activePage === 'orders')
        <!-- ========================================================================= -->
        <!-- SECTION 2: TRA CỨU & QUẢN LÝ ĐƠN HÀNG -->
        <!-- ========================================================================= -->
        <section id="section-orders" class="min-w-0 bg-white rounded-3xl border border-stone-200/80 p-5 sm:p-6 shadow-sm space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-stone-100 pb-4">
                <div class="min-w-0">
                    <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Tra Cứu & Duyệt Đơn Hàng</span>
                    <h2 class="font-serif text-xl sm:text-2xl font-bold text-stone-800">
                        📦 Quản Lý Đơn Đặt Hàng ({{ $orders->count() }} / {{ $allOrders->count() }})
                    </h2>
                </div>

                <!-- Order Search & Status Filter Form -->
                <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                    <input type="text" name="order_search" value="{{ $orderSearch }}" placeholder="Tìm mã đơn, tên khách, SĐT..."
                        class="flex-1 sm:flex-initial h-9 px-3.5 text-xs rounded-xl border border-stone-300 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-300 w-full sm:w-56">
                    <select name="order_status" class="flex-1 sm:flex-initial h-9 px-3 text-xs rounded-xl border border-stone-300 bg-stone-50 focus:bg-white focus:outline-none">
                        <option value="ALL" {{ $orderStatus === 'ALL' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="PENDING" {{ $orderStatus === 'PENDING' ? 'selected' : '' }}>Chờ xác nhận</option>
                        <option value="CONFIRMED" {{ $orderStatus === 'CONFIRMED' ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="SHIPPING" {{ $orderStatus === 'SHIPPING' ? 'selected' : '' }}>Đang giao hàng</option>
                        <option value="COMPLETED" {{ $orderStatus === 'COMPLETED' ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="CANCELLED" {{ $orderStatus === 'CANCELLED' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                    <button type="submit" class="h-9 bg-stone-900 hover:bg-sage-600 text-white text-xs font-semibold px-4 rounded-xl transition whitespace-nowrap cursor-pointer">
                        🔍 Tra cứu đơn
                    </button>
                    @if($orderSearch !== '' || $orderStatus !== 'ALL')
                        <a href="{{ route('admin.orders.index') }}" class="h-9 inline-flex items-center text-xs text-stone-500 hover:text-stone-800 px-2.5 rounded-xl bg-stone-100 hover:bg-stone-200 whitespace-nowrap transition">Xóa lọc</a>
                    @endif
                </form>
            </div>

            @if($orders->isEmpty())
                <div class="text-center py-12 text-sm text-stone-400">
                    Không tìm thấy đơn hàng nào khớp với điều kiện tra cứu.
                </div>
            @else
                <!-- Mobile / Tablet / Small Laptop Responsive Cards (< 1280px) -->
                <div class="xl:hidden space-y-4">
                    @foreach($orders as $order)
                        @php
                            $adminPmBadge = match($order->payment_method) {
                                'BANK_TRANSFER' => ['label' => '🏦 QR Ngân hàng', 'class' => 'bg-blue-50 text-blue-700 border border-blue-200'],
                                'MOMO_QR' => ['label' => '📱 QR MoMo', 'class' => 'bg-pink-50 text-pink-700 border border-pink-200'],
                                default => ['label' => '💵 COD', 'class' => 'bg-stone-100 text-stone-600'],
                            };
                            $isQrOrder = $order->isQrPayment();
                            $isQrPaid = $order->isPaid();
                            $badgeColors = [
                                'PENDING' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'CONFIRMED' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'SHIPPING' => 'bg-purple-100 text-purple-800 border-purple-200',
                                'COMPLETED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'CANCELLED' => 'bg-red-100 text-red-800 border-red-200',
                            ];
                            $statusLabels = [
                                'PENDING' => 'Chờ xác nhận',
                                'CONFIRMED' => 'Đã xác nhận',
                                'SHIPPING' => 'Đang giao hàng',
                                'COMPLETED' => 'Hoàn thành',
                                'CANCELLED' => 'Đã hủy',
                            ];
                            $displayOrderBadgeColor = ($isQrOrder && !$isQrPaid && $order->status !== 'CANCELLED')
                                ? 'bg-amber-100 text-amber-800 border-amber-300'
                                : ($badgeColors[$order->status] ?? 'bg-stone-100 text-stone-700');
                            $displayOrderBadgeLabel = ($isQrOrder && !$isQrPaid && $order->status !== 'CANCELLED')
                                ? 'Chưa thanh toán'
                                : ($statusLabels[$order->status] ?? $order->status);
                        @endphp
                        <div class="p-4 rounded-2xl border border-stone-200 bg-stone-50/70 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-200/80 pb-2.5">
                                <div>
                                    <span class="font-mono font-bold text-sm text-stone-900">#ORDER-{{ $order->id }}</span>
                                    <span class="text-xs text-stone-400 ml-1.5">{{ $order->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                                <span title="{{ ($isQrOrder && !$isQrPaid && $order->status !== 'CANCELLED') ? 'Đơn hàng chưa thanh toán thành công' : $displayOrderBadgeLabel }}" class="text-xs font-bold px-3 py-1 rounded-full border {{ $displayOrderBadgeColor }}">
                                    {{ $displayOrderBadgeLabel }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="space-y-0.5 min-w-0">
                                    <p class="font-bold text-stone-800">{{ $order->customer_name }} • <span class="text-sage-600">{{ $order->phone }}</span></p>
                                    <p class="text-stone-500 break-words">{{ $order->address }}</p>
                                    @if($order->note)
                                        <p class="text-[11px] italic text-amber-800 bg-amber-50 px-2 py-1 rounded-lg mt-1 break-words">
                                            Ghi chú: "{{ $order->note }}"
                                        </p>
                                    @endif
                                </div>
                                <div class="sm:text-right space-y-1">
                                    <p class="text-base font-bold text-sage-600">{{ number_format($order->total_amount, 0, ',', '.') }}₫</p>
                                    <div class="flex flex-wrap sm:justify-end gap-1.5">
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded-full {{ $adminPmBadge['class'] }}">
                                            {{ $adminPmBadge['label'] }}
                                        </span>
                                        @if($isQrOrder && $isQrPaid)
                                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                ✓ Đã TT ({{ $order->transaction_code }})
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <ul class="space-y-1 text-xs bg-white p-3 rounded-xl border border-stone-200/70">
                                @foreach($order->items as $item)
                                    <li class="text-stone-700 break-words">
                                        • <span class="font-mono font-bold text-[11px] text-stone-900">[{{ $item->product->product_code ?? ('LMN-SP' . str_pad((string)$item->product_id, 3, '0', STR_PAD_LEFT)) }}]</span>
                                        <span class="font-medium">{{ $item->product->name ?? 'Sản phẩm #' . $item->product_id }}</span>
                                        <span class="text-sage-600 font-bold">x{{ $item->quantity }}</span>
                                        <span class="text-stone-400">({{ number_format($item->price, 0, ',', '.') }}₫)</span>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                                <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="flex flex-wrap items-center gap-1.5"
                                      data-confirm-status="true"
                                      data-order-id="{{ $order->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="text-xs rounded-xl border border-stone-300 px-2.5 py-1.5 bg-white">
                                        @foreach(['PENDING' => 'Chờ xác nhận', 'CONFIRMED' => 'Đã xác nhận', 'SHIPPING' => 'Đang giao', 'COMPLETED' => 'Hoàn thành', 'CANCELLED' => 'Hủy đơn'] as $stKey => $stVal)
                                            <option value="{{ $stKey }}" {{ $order->status === $stKey ? 'selected' : '' }}>{{ $stVal }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="bg-stone-900 hover:bg-sage-600 text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition cursor-pointer">
                                        Lưu
                                    </button>
                                </form>

                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if($isQrOrder)
                                        <form action="{{ route('admin.orders.verify-payment', $order) }}" method="POST" class="flex flex-wrap items-center gap-1.5"
                                              data-confirm="true"
                                              data-confirm-type="{{ !$isQrPaid ? 'info' : 'warning' }}"
                                              data-confirm-title="{{ !$isQrPaid ? 'Xác nhận đã thanh toán' : 'Đánh dấu chưa thanh toán' }}"
                                              data-confirm-item="#ORDER-{{ $order->id }}"
                                              data-confirm-message="{{ !$isQrPaid ? 'Xác nhận đơn hàng này đã nhận được tiền chuyển khoản?' : 'Bạn muốn chuyển trạng thái thanh toán của đơn hàng này về chưa thanh toán?' }}"
                                              data-confirm-btn="{{ !$isQrPaid ? 'Xác thực chuyển khoản' : 'Xác nhận thay đổi' }}">
                                            @csrf
                                            @method('PATCH')
                                            @if(!$isQrPaid)
                                                <input type="hidden" name="action" value="verify">
                                                <input type="text" name="transaction_code" placeholder="Mã GD..." class="w-28 px-2.5 py-1.5 text-[11px] font-mono uppercase rounded-xl border border-emerald-300 bg-white focus:outline-none">
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold px-2.5 py-1.5 rounded-xl transition cursor-pointer">
                                                    ✓ Xác thực CK
                                                </button>
                                            @else
                                                <input type="hidden" name="action" value="unpaid">
                                                <button type="submit" class="bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-[11px] font-semibold px-2.5 py-1.5 rounded-xl transition cursor-pointer">
                                                    ↩ Chưa TT
                                                </button>
                                            @endif
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST"
                                          data-confirm="true"
                                          data-confirm-type="danger"
                                          data-confirm-title="Xác nhận xóa đơn hàng"
                                          data-confirm-item="#ORDER-{{ $order->id }}"
                                          data-confirm-message="Bạn có chắc chắn muốn xóa đơn hàng này khỏi hệ thống?"
                                          data-confirm-detail="Dữ liệu đơn hàng sẽ bị xóa vĩnh viễn và không thể khôi phục."
                                          data-confirm-btn="Xóa đơn hàng">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:bg-red-50 px-2.5 py-1.5 rounded-xl border border-red-200 bg-white transition cursor-pointer">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Wide Desktop Table (>= 1280px) -->
                <div class="hidden xl:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-stone-200 text-[11px] uppercase tracking-wider text-stone-400">
                                <th class="py-3 px-2.5 w-[115px]">Mã Đơn</th>
                                <th class="py-3 px-2.5 w-[22%]">Khách Hàng & Địa Chỉ</th>
                                <th class="py-3 px-2.5 w-[26%]">Sản Phẩm Đặt Mua</th>
                                <th class="py-3 px-2.5 w-[17%]">Thanh Toán & Voucher</th>
                                <th class="py-3 px-2.5 w-[12%]">Trạng Thái</th>
                                <th class="py-3 px-2.5 w-[190px] text-right">Thao Tác Quản Trị</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach($orders as $order)
                                <tr class="hover:bg-stone-50/80 align-top">
                                    <td class="py-4 px-2.5 font-mono font-bold text-stone-800">
                                        <span class="whitespace-nowrap">#ORDER-{{ $order->id }}</span>
                                        <p class="text-[11px] font-sans font-normal text-stone-400 mt-0.5 whitespace-nowrap">
                                            {{ $order->created_at?->format('d/m/Y H:i') }}
                                        </p>
                                    </td>
                                    <td class="py-4 px-2.5 min-w-0">
                                        <p class="font-bold text-stone-800 break-words">{{ $order->customer_name }}</p>
                                        <p class="text-xs font-semibold text-sage-600">{{ $order->phone }}</p>
                                        <p class="text-xs text-stone-500 mt-1 break-words">{{ $order->address }}</p>
                                        @if($order->note)
                                            <p class="text-[11px] italic text-amber-700 bg-amber-50 px-2 py-1 rounded-lg mt-1 break-words">
                                                Ghi chú: "{{ $order->note }}"
                                            </p>
                                        @endif
                                    </td>
                                    <td class="py-4 px-2.5 min-w-0">
                                        <ul class="space-y-1 text-xs">
                                            @foreach($order->items as $item)
                                                <li class="text-stone-700 break-words">
                                                    • <span class="font-mono font-bold text-[11px] text-stone-900">[{{ $item->product->product_code ?? ('LMN-SP' . str_pad((string)$item->product_id, 3, '0', STR_PAD_LEFT)) }}]</span>
                                                    <span class="font-medium">{{ $item->product->name ?? 'Sản phẩm #' . $item->product_id }}</span>
                                                    <span class="text-sage-600 font-bold">x{{ $item->quantity }}</span>
                                                    <span class="text-stone-400">({{ number_format($item->price, 0, ',', '.') }}₫)</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="py-4 px-2.5 min-w-0">
                                        @php
                                            $adminPmBadge = match($order->payment_method) {
                                                'BANK_TRANSFER' => ['label' => '🏦 QR Ngân hàng', 'class' => 'bg-blue-50 text-blue-700 border border-blue-200'],
                                                'MOMO_QR' => ['label' => '📱 QR MoMo', 'class' => 'bg-pink-50 text-pink-700 border border-pink-200'],
                                                default => ['label' => '💵 COD', 'class' => 'bg-stone-100 text-stone-600'],
                                            };
                                            $isQrOrder = $order->isQrPayment();
                                            $isQrPaid = $order->isPaid();
                                        @endphp
                                        <p class="text-sm sm:text-base font-bold tabular-nums text-sage-600 whitespace-nowrap">{{ number_format($order->total_amount, 0, ',', '.') }}₫</p>
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded-full {{ $adminPmBadge['class'] }} mt-1">
                                            {{ $adminPmBadge['label'] }}
                                        </span>
                                        @if($isQrOrder && $isQrPaid)
                                            <p class="mt-1">
                                                <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 break-all">
                                                    ✓ Đã TT ({{ $order->transaction_code }})
                                                </span>
                                            </p>
                                        @elseif($isQrOrder)
                                            <p class="mt-1">
                                                <span title="Đơn hàng chưa thanh toán thành công" class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-300">
                                                    ⏳ Chưa thanh toán
                                                </span>
                                            </p>
                                        @endif
                                        @if($order->coupon_code)
                                            <p class="text-[11px] text-emerald-600 font-semibold mt-1">
                                                🎟️ {{ $order->coupon_code }} (-{{ number_format($order->discount_amount, 0, ',', '.') }}₫)
                                            </p>
                                        @endif
                                    </td>
                                    <td class="py-4 px-2.5">
                                        @php
                                            $badgeColors = [
                                                'PENDING' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                'CONFIRMED' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'SHIPPING' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                'COMPLETED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                'CANCELLED' => 'bg-red-100 text-red-800 border-red-200',
                                            ];
                                            $statusLabels = [
                                                'PENDING' => 'Chờ xác nhận',
                                                'CONFIRMED' => 'Đã xác nhận',
                                                'SHIPPING' => 'Đang giao hàng',
                                                'COMPLETED' => 'Hoàn thành',
                                                'CANCELLED' => 'Đã hủy',
                                            ];
                                            $displayOrderBadgeColor = ($isQrOrder && !$isQrPaid && $order->status !== 'CANCELLED')
                                                ? 'bg-amber-100 text-amber-800 border-amber-300'
                                                : ($badgeColors[$order->status] ?? 'bg-stone-100 text-stone-700');
                                            $displayOrderBadgeLabel = ($isQrOrder && !$isQrPaid && $order->status !== 'CANCELLED')
                                                ? 'Chưa thanh toán'
                                                : ($statusLabels[$order->status] ?? $order->status);
                                        @endphp
                                        <span title="{{ ($isQrOrder && !$isQrPaid && $order->status !== 'CANCELLED') ? 'Đơn hàng chưa thanh toán thành công' : $displayOrderBadgeLabel }}" class="inline-block text-[11px] font-bold px-2.5 py-1 rounded-full border {{ $displayOrderBadgeColor }}">
                                            {{ $displayOrderBadgeLabel }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-2.5 text-right">
                                        <div class="flex flex-col items-end gap-2">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="flex items-center gap-1"
                                                      data-confirm-status="true"
                                                      data-order-id="{{ $order->id }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" class="w-28 text-xs rounded-xl border border-stone-300 px-2 py-1.5 bg-white">
                                                        @foreach(['PENDING' => 'Chờ xác nhận', 'CONFIRMED' => 'Đã xác nhận', 'SHIPPING' => 'Đang giao', 'COMPLETED' => 'Hoàn thành', 'CANCELLED' => 'Hủy đơn'] as $stKey => $stVal)
                                                            <option value="{{ $stKey }}" {{ $order->status === $stKey ? 'selected' : '' }}>{{ $stVal }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="bg-stone-900 hover:bg-sage-600 text-white text-xs font-semibold px-2.5 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                                                        Lưu
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.orders.destroy', $order) }}" method="POST"
                                                      data-confirm="true"
                                                      data-confirm-type="danger"
                                                      data-confirm-title="Xác nhận xóa đơn hàng"
                                                      data-confirm-item="#ORDER-{{ $order->id }}"
                                                      data-confirm-message="Bạn có chắc chắn muốn xóa đơn hàng này khỏi hệ thống?"
                                                      data-confirm-detail="Dữ liệu đơn hàng sẽ bị xóa vĩnh viễn và không thể khôi phục."
                                                      data-confirm-btn="Xóa đơn hàng">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-500 hover:bg-red-50 px-2 py-1.5 rounded-xl border border-red-200 transition cursor-pointer whitespace-nowrap">
                                                        Xóa
                                                    </button>
                                                </form>
                                            </div>

                                            @if($isQrOrder)
                                                <form action="{{ route('admin.orders.verify-payment', $order) }}" method="POST" class="flex items-center justify-end gap-1"
                                                      data-confirm="true"
                                                      data-confirm-type="{{ !$isQrPaid ? 'info' : 'warning' }}"
                                                      data-confirm-title="{{ !$isQrPaid ? 'Xác nhận đã thanh toán' : 'Đánh dấu chưa thanh toán' }}"
                                                      data-confirm-item="#ORDER-{{ $order->id }}"
                                                      data-confirm-message="{{ !$isQrPaid ? 'Xác nhận đơn hàng này đã nhận được tiền chuyển khoản?' : 'Bạn muốn chuyển trạng thái thanh toán của đơn hàng này về chưa thanh toán?' }}"
                                                      data-confirm-btn="{{ !$isQrPaid ? 'Xác thực chuyển khoản' : 'Xác nhận thay đổi' }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    @if(!$isQrPaid)
                                                        <input type="hidden" name="action" value="verify">
                                                        <input type="text" name="transaction_code" placeholder="Mã GD..." class="w-24 px-2 py-1 text-[11px] font-mono uppercase rounded-lg border border-emerald-300 bg-white focus:outline-none">
                                                        <button type="submit" title="Xác thực đã chuyển khoản" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold px-2.5 py-1 rounded-lg transition cursor-pointer whitespace-nowrap">
                                                            ✓ Xác thực CK
                                                        </button>
                                                    @else
                                                        <input type="hidden" name="action" value="unpaid">
                                                        <button type="submit" title="Đánh dấu chưa thanh toán" class="bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-[11px] font-semibold px-2.5 py-1 rounded-lg transition cursor-pointer whitespace-nowrap">
                                                            ↩ Chưa TT
                                                        </button>
                                                    @endif
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
        @endif


        @if($activePage === 'products')
        <!-- ========================================================================= -->
        <!-- SECTION 3: XEM, THÊM, SỬA, XÓA SẢN PHẨM MỸ PHẨM ĐẦY ĐỦ -->
        <!-- ========================================================================= -->
        <section id="section-products" class="grid grid-cols-1 xl:grid-cols-12 gap-5 lg:gap-6 items-start">
            <!-- Left: Add / Edit Product Form -->
            <div class="xl:col-span-5 min-w-0 bg-white rounded-3xl border border-stone-200/80 p-5 sm:p-6 shadow-sm h-fit space-y-4">
                <div class="border-b border-stone-100 pb-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="min-w-0">
                        <span class="text-xs font-bold uppercase tracking-widest text-sage-500">
                            {{ $editingProduct ? 'Chỉnh Sửa Sản Phẩm' : 'Thêm Sản Phẩm Mới' }}
                        </span>
                        <h3 class="font-serif text-lg sm:text-xl font-bold text-stone-800 break-words">
                            {{ $editingProduct ? $editingProduct->name : 'Nhập Mỹ Phẩm Vào Kho' }}
                        </h3>
                    </div>
                    @if($editingProduct)
                        <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-stone-500 hover:text-stone-800 bg-stone-100 px-3 py-1.5 rounded-xl flex-shrink-0">
                            ✕ Hủy sửa
                        </a>
                    @endif
                </div>

                <form action="{{ $editingProduct ? route('admin.products.update', $editingProduct) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
                      @if($editingProduct)
                          data-confirm="true"
                          data-confirm-type="info"
                          data-confirm-title="Lưu thay đổi sản phẩm"
                          data-confirm-item="{{ $editingProduct->name }}"
                          data-confirm-message="Bạn có chắc chắn muốn cập nhật thông tin sản phẩm này?"
                          data-confirm-btn="Lưu thay đổi"
                      @endif>
                    @csrf
                    @if($editingProduct)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Mã SP (SKU) *</label>
                            <input type="text" name="sku" value="{{ old('sku', $editingProduct->product_code ?? ('LMN-SP' . str_pad((string)($allProducts->count() + 1), 3, '0', STR_PAD_LEFT))) }}" placeholder="LMN-SP013" class="w-full px-3 py-2 text-sm font-mono font-bold uppercase rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-300">
                        </div>
                        <div class="sm:col-span-8">
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Tên sản phẩm mỹ phẩm *</label>
                            <input type="text" name="name" required value="{{ old('name', $editingProduct->name ?? '') }}" placeholder="VD: Tinh Chất Phục Hồi B5 La Roche-Posay" class="w-full px-3.5 py-2 text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Giá gốc (VNĐ) *</label>
                            <input type="number" name="price" required value="{{ old('price', $editingProduct->price ?? '') }}" placeholder="650000" class="w-full px-3.5 py-2 text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Giá khuyến mãi (VNĐ)</label>
                            <input type="number" name="sale_price" value="{{ old('sale_price', $editingProduct->sale_price ?? '') }}" placeholder="520000" class="w-full px-3.5 py-2 text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Danh mục *</label>
                            <select name="category_id" required class="w-full px-3.5 py-2 text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (old('category_id', $editingProduct->category_id ?? '') == $cat->id) ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Thương hiệu *</label>
                            <select name="brand_id" required class="w-full px-3.5 py-2 text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ (old('brand_id', $editingProduct->brand_id ?? '') == $brand->id) ? 'selected' : '' }}>
                                        {{ $brand->name }} ({{ $brand->origin }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Loại da *</label>
                            <select name="skin_type" class="w-full px-2.5 py-2 text-xs sm:text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                                @foreach(['Mọi loại da', 'Da dầu', 'Da khô', 'Da nhạy cảm'] as $st)
                                    <option value="{{ $st }}" {{ (old('skin_type', $editingProduct->skin_type ?? '') === $st) ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Dung tích *</label>
                            <input type="text" name="volume" required value="{{ old('volume', $editingProduct->volume ?? '30ml') }}" placeholder="30ml / 50g" class="w-full px-2.5 py-2 text-xs sm:text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Tồn kho *</label>
                            <input type="number" name="stock" required min="0" value="{{ old('stock', $editingProduct->stock ?? 20) }}" class="w-full px-2.5 py-2 text-xs sm:text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-stone-600 mb-1.5">Hình ảnh sản phẩm (Chọn từ máy) *</label>
                        <div class="p-3.5 rounded-2xl border-2 border-dashed border-sage-200 bg-sage-50/40 space-y-3">
                            <div class="flex items-center gap-3.5">
                                <div id="product-image-preview-wrap" class="{{ $editingProduct ? 'block' : 'hidden' }} relative flex-shrink-0">
                                    <img id="product-image-preview"
                                         src="{{ $editingProduct ? $editingProduct->first_image : '' }}"
                                         alt="Preview"
                                         class="w-16 h-16 rounded-2xl object-cover bg-white border border-sage-200 shadow-sm">
                                </div>
                                <div class="flex-1 min-w-0 space-y-1.5">
                                    <label for="product-image-input" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-sage-500 hover:bg-sage-600 text-white text-xs font-semibold cursor-pointer shadow-sm transition">
                                        <span>📁 Chọn ảnh từ máy tính</span>
                                    </label>
                                    <input type="file" id="product-image-input" name="image_file" accept="image/*" class="hidden" onchange="previewSelectedProductImage(this)">
                                    <p id="product-image-filename" class="text-[11px] text-stone-500 truncate">
                                        {{ $editingProduct ? 'Đang dùng ảnh hiện tại (Chọn tệp mới để thay thế)' : 'Hỗ trợ JPG, PNG, WEBP, GIF (Tối đa 5MB)' }}
                                    </p>
                                </div>
                                <button type="button" id="clear-selected-image-btn" onclick="clearSelectedProductImage()" class="hidden text-[11px] font-semibold text-red-600 hover:bg-red-50 px-2.5 py-1.5 rounded-xl border border-red-200 transition cursor-pointer">
                                    ✕ Bỏ chọn
                                </button>
                            </div>

                            @if($editingProduct)
                                <label class="flex items-center gap-2 pt-2 border-t border-sage-200/60 text-xs text-red-600 font-medium cursor-pointer">
                                    <input type="checkbox" id="remove-existing-image" name="remove_image" value="1" class="rounded accent-red-500" onchange="toggleRemoveExistingImage(this)">
                                    <span>🗑️ Xóa ảnh hiện tại của sản phẩm này</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Bảng thành phần (Ingredients)</label>
                        <textarea name="ingredients" rows="2" placeholder="Aqua, Niacinamide 10%, Zinc PCA 1%..." class="w-full px-3.5 py-2 text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">{{ old('ingredients', $editingProduct->ingredients ?? '') }}</textarea>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold uppercase text-stone-600">Mô tả công dụng chi tiết (Rich Text & Hình ảnh)</label>
                            <span id="desc-upload-status" class="text-[11px] font-semibold text-sage-600 hidden">⏳ Đang tải ảnh lên...</span>
                        </div>

                        <div class="rounded-2xl border border-stone-200 bg-white overflow-hidden focus-within:ring-2 focus-within:ring-sage-300">
                            <!-- Rich Text Toolbar -->
                            <div class="flex flex-wrap items-center gap-1 p-2 bg-stone-100 border-b border-stone-200 text-xs">
                                <button type="button" onclick="execDescCmd('bold')" title="In đậm (Ctrl+B)" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-sage-50 border border-stone-200 font-bold text-stone-700 cursor-pointer">
                                    B
                                </button>
                                <button type="button" onclick="execDescCmd('italic')" title="In nghiêng (Ctrl+I)" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-sage-50 border border-stone-200 italic font-serif text-stone-700 cursor-pointer">
                                    I
                                </button>
                                <button type="button" onclick="execDescCmd('underline')" title="Gạch chân (Ctrl+U)" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-sage-50 border border-stone-200 underline text-stone-700 cursor-pointer">
                                    U
                                </button>

                                <span class="h-4 w-px bg-stone-300 mx-0.5"></span>

                                <button type="button" onclick="execDescCmd('formatBlock', 'H2')" title="Tiêu đề lớn" class="px-2 py-1.5 rounded-lg bg-white hover:bg-sage-50 border border-stone-200 font-bold text-stone-700 cursor-pointer">
                                    H2
                                </button>
                                <button type="button" onclick="execDescCmd('formatBlock', 'H3')" title="Tiêu đề phụ" class="px-2 py-1.5 rounded-lg bg-white hover:bg-sage-50 border border-stone-200 font-bold text-sage-700 cursor-pointer">
                                    H3
                                </button>
                                <button type="button" onclick="execDescCmd('formatBlock', 'P')" title="Đoạn văn thường" class="px-2 py-1.5 rounded-lg bg-white hover:bg-sage-50 border border-stone-200 text-stone-600 cursor-pointer">
                                    P
                                </button>

                                <span class="h-4 w-px bg-stone-300 mx-0.5"></span>

                                <button type="button" onclick="execDescCmd('insertUnorderedList')" title="Danh sách dấu chấm" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-sage-50 border border-stone-200 text-stone-700 cursor-pointer">
                                    • Danh sách
                                </button>
                                <button type="button" onclick="execDescCmd('insertOrderedList')" title="Danh sách đánh số" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-sage-50 border border-stone-200 text-stone-700 cursor-pointer">
                                    1. Danh sách
                                </button>

                                <span class="h-4 w-px bg-stone-300 mx-0.5"></span>

                                <button type="button" onclick="execDescCmd('foreColor', '#397A63')" title="Màu chữ Sage Green" class="px-2 py-1.5 rounded-lg bg-white hover:bg-sage-50 border border-stone-200 font-bold text-sage-600 cursor-pointer">
                                    A
                                </button>
                                <button type="button" onclick="execDescCmd('removeFormat')" title="Xóa định dạng" class="px-2 py-1.5 rounded-lg bg-white hover:bg-stone-200 border border-stone-200 text-stone-500 cursor-pointer">
                                    Tx
                                </button>

                                <span class="h-4 w-px bg-stone-300 mx-0.5"></span>

                                <!-- Insert Image from Computer Button -->
                                <label for="desc-editor-image-input" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sage-500 hover:bg-sage-600 text-white font-semibold cursor-pointer shadow-sm transition">
                                    <span>🖼️ Chèn ảnh từ máy</span>
                                </label>
                                <input type="file" id="desc-editor-image-input" accept="image/*" multiple class="hidden" onchange="uploadDescEditorImages(this.files)">
                            </div>

                            <!-- Editable Rich Content Area -->
                            <div id="desc-editor"
                                 contenteditable="true"
                                 data-placeholder="Nhập mô tả chi tiết công dụng, hướng dẫn sử dụng và bấm '🖼️ Chèn ảnh từ máy' (hoặc kéo-thả / dán ảnh trực tiếp vào đây)..."
                                 oninput="syncDescEditor()"
                                 class="w-full min-h-[150px] max-h-[300px] overflow-y-auto admin-content-scroll px-4 py-3 text-sm text-stone-700 leading-relaxed focus:outline-none bg-stone-50/40 focus:bg-white">{!! old('description', $editingProduct->description ?? '') !!}</div>
                        </div>

                        <textarea name="description" id="description-hidden-input" class="hidden">{{ old('description', $editingProduct->description ?? '') }}</textarea>
                        <p class="text-[11px] text-stone-400 mt-1">
                            💡 Có thể bôi đậm, tạo danh sách và chèn nhiều ảnh minh họa trực tiếp vào nội dung mô tả.
                        </p>
                    </div>

                    <label class="flex items-center gap-2 text-xs font-semibold text-stone-700 cursor-pointer">
                        <input type="checkbox" name="is_best_seller" value="1" {{ old('is_best_seller', $editingProduct->is_best_seller ?? false) ? 'checked' : '' }} class="rounded accent-sage-500">
                        <span>Đánh dấu là Sản phẩm Bán chạy (Best Seller)</span>
                    </label>

                    <button type="submit" class="w-full bg-sage-500 hover:bg-sage-600 text-white font-semibold py-3.5 rounded-2xl shadow-md shadow-sage-200 transition text-sm cursor-pointer">
                        {{ $editingProduct ? '💾 Lưu Cập Nhật Sản Phẩm' : '+ Thêm Mỹ Phẩm Vào Kho' }}
                    </button>
                </form>
            </div>

            <!-- Right: Product List with Search & Stock Filter -->
            <div class="xl:col-span-7 min-w-0 bg-white rounded-3xl border border-stone-200/80 p-5 sm:p-6 shadow-sm space-y-4">
                <div class="border-b border-stone-100 pb-4 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Quản Lý Kho Hàng</span>
                            <h3 class="font-serif text-xl font-bold text-stone-800">
                                Danh Sách Sản Phẩm ({{ $products->count() }} / {{ $allProducts->count() }})
                            </h3>
                        </div>
                        @if($categoryFilter !== 'ALL' || $stockFilter !== 'ALL' || $productSearch !== '')
                            <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-stone-500 hover:text-stone-800 bg-stone-100 hover:bg-stone-200 px-3 py-1.5 rounded-xl transition whitespace-nowrap">
                                ✕ Xóa bộ lọc
                            </a>
                        @endif
                    </div>

                    <!-- Filter Tabs for Stock Status -->
                    <div class="flex flex-wrap gap-1.5 text-xs">
                        @foreach([
                            'ALL' => 'Tất cả (' . $allProducts->count() . ')',
                            'BEST_SELLER' => '★ Bán chạy',
                            'OUT_OF_STOCK' => '🔴 Hết hàng (' . $stats['out_of_stock_count'] . ')',
                            'LOW_STOCK' => '🟠 Sắp hết (' . $stats['low_stock_count'] . ')',
                            'IN_STOCK' => '🟢 Còn nhiều',
                        ] as $sfKey => $sfLabel)
                            <a href="{{ route('admin.products.index', ['stock_filter' => $sfKey, 'product_search' => $productSearch, 'category_filter' => $categoryFilter]) }}"
                                class="px-2.5 py-1.5 rounded-xl font-semibold transition whitespace-nowrap {{ $stockFilter === $sfKey ? 'bg-stone-900 text-white shadow-sm' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                                {{ $sfLabel }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Product Search Bar -->
                <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                    <input type="hidden" name="stock_filter" value="{{ $stockFilter }}">
                    <input type="text" name="product_search" value="{{ $productSearch }}" placeholder="Tìm tên sản phẩm, thương hiệu, loại da..."
                        class="min-w-0 flex-1 h-9 px-3.5 text-xs rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-300">
                    <select name="category_filter" class="h-9 px-3 text-xs rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                        <option value="ALL">Mọi danh mục</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ (string)$categoryFilter === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="h-9 bg-stone-900 hover:bg-sage-600 text-white text-xs font-semibold px-4 rounded-xl transition whitespace-nowrap cursor-pointer">
                        Lọc SP
                    </button>
                </form>

                <div class="space-y-2.5 max-h-[760px] overflow-y-auto admin-content-scroll pr-1">
                    @forelse($products as $product)
                        @php $isVisible = (bool) ($product->is_active ?? true); @endphp
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 p-3.5 rounded-2xl border {{ !$isVisible ? 'border-stone-300 bg-stone-100/80 opacity-80' : ($product->stock <= 0 ? 'border-red-200 bg-red-50/30' : ($product->stock <= 15 ? 'border-orange-200 bg-orange-50/30' : 'border-stone-200/70 bg-stone-50/50')) }} hover:border-sage-300 transition">
                            <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                <img src="{{ $product->first_image }}" alt="{{ $product->name }}" class="w-14 h-14 rounded-2xl object-cover bg-white border border-stone-200 flex-shrink-0 {{ !$isVisible ? 'grayscale' : '' }}">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="font-mono font-bold text-[10px] bg-stone-900 text-white px-2 py-0.5 rounded-md">
                                            {{ $product->product_code }}
                                        </span>
                                        <span class="text-[10px] font-bold uppercase text-sage-500">{{ $product->brand->name ?? '' }}</span>
                                        <span class="text-[10px] text-stone-400">• {{ $product->category->name ?? '' }}</span>
                                        @if(!$isVisible)
                                            <span class="text-[10px] bg-stone-700 text-white font-bold px-2 py-0.5 rounded-full">ĐÃ ẨN</span>
                                        @endif
                                        @if($product->is_best_seller)
                                            <span class="text-[10px] bg-[#D4728C] text-white border border-[#C25E79] font-bold px-2 py-0.5 rounded-full">✦ Best Seller</span>
                                        @endif
                                        @if($product->stock <= 0)
                                            <span class="text-[10px] bg-red-600 text-white font-bold px-2 py-0.5 rounded-full">HẾT HÀNG</span>
                                        @elseif($product->stock <= 15)
                                            <span class="text-[10px] bg-orange-500 text-white font-bold px-2 py-0.5 rounded-full">SẮP HẾT ({{ $product->stock }} SP)</span>
                                        @else
                                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">Tồn kho: {{ $product->stock }} SP</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="text-sm font-bold text-stone-800 hover:text-sage-600 line-clamp-1 mt-0.5">
                                        {{ $product->name }}
                                    </a>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs mt-0.5">
                                        <span class="font-bold text-sage-600">{{ number_format($product->final_price, 0, ',', '.') }}₫</span>
                                        @if($product->sale_price)
                                            <span class="line-through text-stone-400">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                        @endif
                                        <span class="text-stone-500">Đã bán: <strong>{{ $product->sold_quantity ?? 0 }}</strong></span>
                                        <span class="text-stone-400">{{ $product->volume }} • {{ $product->skin_type }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 flex-shrink-0">
                                <form action="{{ route('admin.products.toggle-visibility', $product) }}" method="POST"
                                      data-confirm="true"
                                      data-confirm-type="{{ $isVisible ? 'warning' : 'info' }}"
                                      data-confirm-title="{{ $isVisible ? 'Xác nhận ẩn sản phẩm' : 'Xác nhận hiển thị sản phẩm' }}"
                                      data-confirm-item="{{ $product->name }}"
                                      data-confirm-message="{{ $isVisible ? 'Sản phẩm này sẽ bị ẩn khỏi trang chủ và cửa hàng. Bạn có chắc muốn ẩn?' : 'Sản phẩm này sẽ hiển thị trở lại trên trang chủ và cửa hàng?' }}"
                                      data-confirm-btn="{{ $isVisible ? 'Ẩn sản phẩm' : 'Hiển thị sản phẩm' }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            title="{{ $isVisible ? 'Gạt để ẩn sản phẩm khỏi trang chủ' : 'Gạt để hiện sản phẩm lên trang chủ' }}"
                                            class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-xl border text-xs font-semibold transition whitespace-nowrap cursor-pointer {{ $isVisible ? 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' : 'bg-stone-200/80 border-stone-300 text-stone-600 hover:bg-stone-200' }}">
                                        <span class="relative inline-flex h-4 w-7 items-center rounded-full transition-colors {{ $isVisible ? 'bg-emerald-500' : 'bg-stone-400' }}">
                                            <span class="inline-block h-3 w-3 transform rounded-full bg-white shadow transition-transform {{ $isVisible ? 'translate-x-3.5' : 'translate-x-0.5' }}"></span>
                                        </span>
                                        <span>{{ $isVisible ? 'Hiện' : 'Ẩn' }}</span>
                                    </button>
                                </form>

                                <a href="{{ route('admin.products.index', ['edit_product' => $product->id]) }}" class="text-xs font-semibold bg-white hover:bg-sage-50 text-stone-700 border border-stone-200 px-3 py-1.5 rounded-xl transition whitespace-nowrap">
                                    Sửa
                                </a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                      data-confirm="true"
                                      data-confirm-type="danger"
                                      data-confirm-title="Xác nhận xóa sản phẩm"
                                      data-confirm-item="{{ $product->name }}"
                                      data-confirm-message="Bạn có chắc chắn muốn xóa sản phẩm này khỏi cửa hàng?"
                                      data-confirm-detail="Sản phẩm và dữ liệu liên quan sẽ bị xóa vĩnh viễn và không thể khôi phục."
                                      data-confirm-btn="Xóa sản phẩm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-xl transition whitespace-nowrap cursor-pointer">
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-10 text-xs text-stone-400">Không có sản phẩm nào khớp với bộ lọc.</p>
                    @endforelse
                </div>
            </div>
        </section>
        @endif


        @if($activePage === 'coupons')
        <!-- ========================================================================= -->
        <!-- SECTION 4: QUẢN LÝ MÃ GIẢM GIÁ (VOUCHER) & DANH MỤC -->
        <!-- ========================================================================= -->
        <section id="section-coupons" class="grid grid-cols-1 xl:grid-cols-12 gap-5 lg:gap-6 items-start">
            <div class="xl:col-span-5 min-w-0 bg-white rounded-3xl border border-stone-200/80 p-5 sm:p-6 shadow-sm h-fit space-y-4">
                <div class="border-b border-stone-100 pb-3">
                    <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Khuyến Mãi Cho Khách Hàng</span>
                    <h3 class="font-serif text-xl font-bold text-stone-800">Phát Hành Voucher Mới</h3>
                </div>

                <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Mã Voucher *</label>
                            <input type="text" name="code" required placeholder="VD: VIP25" class="w-full px-3.5 py-2.5 text-sm font-mono uppercase rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Giảm (%) *</label>
                            <input type="number" name="discount_percent" required min="1" max="90" value="20" class="w-full px-3.5 py-2.5 text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Mô tả ưu đãi (Hiển thị ở Ví Voucher Khách hàng)</label>
                        <input type="text" name="description" placeholder="VD: Giảm 20% toàn bộ sản phẩm chăm sóc da" class="w-full px-3.5 py-2.5 text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Đơn tối thiểu (VNĐ)</label>
                            <input type="number" name="min_order_amount" value="200000" class="w-full px-3.5 py-2.5 text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-stone-600 mb-1">Hạn sử dụng *</label>
                            <input type="date" name="expires_at" required value="{{ now()->addMonths(3)->format('Y-m-d') }}" class="w-full px-3.5 py-2.5 text-sm rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-stone-900 hover:bg-sage-600 text-white font-semibold py-3 rounded-2xl transition text-sm cursor-pointer">
                        🎟️ Tạo Mã Giảm Giá Mới
                    </button>
                </form>
            </div>

            <div class="xl:col-span-7 min-w-0 bg-white rounded-3xl border border-stone-200/80 p-5 sm:p-6 shadow-sm space-y-4">
                <div class="border-b border-stone-100 pb-3">
                    <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Danh Sách Mã Giảm Giá</span>
                    <h3 class="font-serif text-xl font-bold text-stone-800">Các Voucher Đang Phát Hành ({{ $coupons->count() }})</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    @foreach($coupons as $coupon)
                        <div class="flex flex-col justify-between gap-3 p-4 rounded-2xl border min-w-0 {{ $coupon->is_active ? 'border-terracotta-200 bg-gradient-to-br from-terracotta-50 via-white to-terracotta-50/50' : 'border-stone-200 bg-stone-100 opacity-75' }}">
                            <div class="min-w-0 space-y-1.5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="font-mono font-bold text-base text-terracotta-900 break-all">{{ $coupon->code }}</span>
                                    <span class="text-xs font-bold bg-terracotta-600 text-white px-2.5 py-0.5 rounded-full whitespace-nowrap">
                                        -{{ $coupon->discount_percent }}%
                                    </span>
                                </div>
                                <p class="text-xs text-stone-600 line-clamp-2 min-h-[32px]">{{ $coupon->description }}</p>
                                @if($coupon->min_order_amount > 0)
                                    <p class="text-[11px] text-stone-500">
                                        Đơn tối thiểu: <strong class="text-stone-700">{{ number_format($coupon->min_order_amount, 0, ',', '.') }}₫</strong>
                                    </p>
                                @endif
                            </div>

                            <div class="flex items-center justify-between gap-2 pt-2.5 border-t border-stone-200/70">
                                <p class="text-[11px] text-stone-500 truncate">
                                    HSD: {{ $coupon->expires_at?->format('d/m/Y') }}
                                    • <strong class="{{ $coupon->is_active ? 'text-terracotta-700' : 'text-stone-500' }}">{{ $coupon->is_active ? 'Đang bật' : 'Đã tắt' }}</strong>
                                </p>

                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <form action="{{ route('admin.coupons.toggle', $coupon) }}" method="POST"
                                          data-confirm="true"
                                          data-confirm-type="warning"
                                          data-confirm-title="Thay đổi trạng thái mã giảm giá"
                                          data-confirm-item="{{ $coupon->code }}"
                                          data-confirm-message="Bạn muốn {{ $coupon->is_active ? 'tạm tắt' : 'kích hoạt lại' }} mã giảm giá này?"
                                          data-confirm-btn="{{ $coupon->is_active ? 'Tắt mã' : 'Bật mã' }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-semibold px-2.5 py-1.5 rounded-xl border border-stone-300 bg-white hover:bg-stone-50 whitespace-nowrap cursor-pointer">
                                            {{ $coupon->is_active ? 'Tắt' : 'Bật' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST"
                                          data-confirm="true"
                                          data-confirm-type="danger"
                                          data-confirm-title="Xác nhận xóa mã giảm giá"
                                          data-confirm-item="{{ $coupon->code }}"
                                          data-confirm-message="Bạn có chắc chắn muốn xóa mã giảm giá này?"
                                          data-confirm-detail="Khách hàng sẽ không thể áp dụng mã này nữa sau khi xóa."
                                          data-confirm-btn="Xóa mã">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold px-2.5 py-1.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 whitespace-nowrap cursor-pointer">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif


        @if($activePage === 'payments')
        <!-- =================================================================
             5. QUẢN LÝ GIAO DỊCH NGÂN HÀNG & ĐỐI SOÁT SEPAY WEBHOOK
             ================================================================= -->
        @php
            $txList = $paymentTransactions ?? collect();
            $matchedCount = $txList->whereIn('status', ['MATCHED_PAID', 'MANUAL_RECONCILED'])->count();
            $unreconciledCount = $txList->filter(fn($t) => $t->needsReconciliation())->count();
            $totalReceivedAmount = $txList->where('transfer_type', 'in')->sum('amount');
        @endphp
        <section class="space-y-6 min-w-0">
            <div class="bg-white rounded-3xl border border-stone-200 p-5 sm:p-6 shadow-sm space-y-5 min-w-0">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-stone-100 pb-4">
                    <div class="min-w-0">
                        <span class="text-xs font-bold uppercase tracking-widest text-sage-600">Cổng Thanh Toán Tự Động SePay × VietQR</span>
                        <h1 class="text-xl sm:text-2xl font-bold text-stone-800 mt-0.5 break-words">
                            Quản Lý Giao Dịch Ngân Hàng &amp; Đối Soát Thanh Toán
                        </h1>
                        <p class="text-xs text-stone-500 mt-1">
                            Theo dõi toàn bộ biến động số dư từ SePay Webhook, kiểm tra chi tiết JSON payload, lịch sử thay đổi trạng thái và xử lý các giao dịch chưa đối soát.
                        </p>
                    </div>
                    <div class="text-left sm:text-right text-xs bg-sage-50 border border-sage-200 rounded-2xl px-4 py-2.5 max-w-full min-w-0">
                        <span class="text-stone-500 block">Webhook Endpoint:</span>
                        <code class="font-mono font-bold text-sage-700 break-all">POST {{ url('/api/webhooks/sepay') }}</code>
                    </div>
                </div>

                <!-- Thống kê nhanh giao dịch -->
                <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
                    <div class="min-w-0 p-4 rounded-2xl bg-stone-50 border border-stone-200">
                        <span class="text-xs text-stone-500 block truncate">Tổng giao dịch Webhook</span>
                        <p class="text-lg sm:text-2xl font-bold tabular-nums text-stone-800 mt-1 truncate">{{ $txList->count() }}</p>
                    </div>
                    <div class="min-w-0 p-4 rounded-2xl bg-emerald-50 border border-emerald-200">
                        <span class="text-xs text-emerald-700 font-semibold block truncate">Đã khớp &amp; Xác nhận PAID</span>
                        <p class="text-lg sm:text-2xl font-bold tabular-nums text-emerald-700 mt-1 truncate">{{ $matchedCount }}</p>
                    </div>
                    <div class="min-w-0 p-4 rounded-2xl bg-amber-50 border border-amber-200">
                        <span class="text-xs text-amber-800 font-semibold block truncate">Cần kiểm tra / Đối soát</span>
                        <p class="text-lg sm:text-2xl font-bold tabular-nums text-amber-700 mt-1 truncate">{{ $unreconciledCount }}</p>
                    </div>
                    <div class="min-w-0 p-4 rounded-2xl bg-blue-50 border border-blue-200">
                        <span class="text-xs text-blue-700 font-semibold block truncate">Tổng tiền vào ghi nhận</span>
                        <p class="text-lg sm:text-2xl font-bold tabular-nums text-blue-700 mt-1 truncate">{{ number_format($totalReceivedAmount, 0, ',', '.') }}₫</p>
                    </div>
                </div>

                @if($txList->isEmpty())
                    <div class="p-10 text-center rounded-2xl bg-stone-50 border border-stone-200 text-xs text-stone-500">
                        Chưa có giao dịch Webhook nào được ghi nhận trong bảng <code>payment_transactions</code>.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($txList as $tx)
                            @php
                                $txOrder = $tx->order;
                                $txPayment = $tx->payment;
                                $isPaidTx = in_array($tx->status, ['MATCHED_PAID', 'MANUAL_RECONCILED'], true);
                                $needsRec = $tx->needsReconciliation();
                            @endphp
                            <div class="rounded-2xl border {{ $isPaidTx ? 'border-emerald-200 bg-emerald-50/20' : ($needsRec ? 'border-amber-300 bg-amber-50/30' : 'border-stone-200 bg-white') }} p-5 space-y-4">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-stone-900 text-white">
                                                GD #{{ $tx->provider_transaction_id }}
                                            </span>
                                            @if($tx->reference_code)
                                                <span class="font-mono text-xs font-semibold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200">
                                                    Ref: {{ $tx->reference_code }}
                                                </span>
                                            @endif
                                            <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-stone-100 text-stone-700">
                                                🏦 {{ $tx->gateway ?: 'Ngân hàng' }} ({{ $tx->account_number ?: 'N/A' }})
                                            </span>
                                            <span class="text-xs font-bold px-3 py-1 rounded-full {{ $isPaidTx ? 'bg-emerald-600 text-white' : ($needsRec ? 'bg-amber-500 text-white' : 'bg-stone-200 text-stone-700') }}">
                                                {{ $tx->status_label }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-stone-600 pt-1 break-words">
                                            <strong>Nội dung CK:</strong> <code class="font-mono bg-white px-2 py-0.5 rounded border border-stone-200 break-all">{{ $tx->transfer_content ?: '(Trống)' }}</code>
                                            • <strong>Thời gian nhận:</strong> {{ $tx->transaction_date?->format('d/m/Y H:i:s') ?? $tx->created_at?->format('d/m/Y H:i:s') }}
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <span class="text-[11px] text-stone-400 block">Số tiền thực nhận</span>
                                        <strong class="text-lg font-bold {{ $tx->transfer_type === 'in' ? 'text-emerald-700' : 'text-stone-600' }}">
                                            {{ $tx->transfer_type === 'in' ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', '.') }}₫
                                        </strong>
                                    </div>
                                </div>

                                <!-- Thông tin đơn hàng & khách hàng liên kết -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs bg-white p-3.5 rounded-xl border border-stone-200">
                                    <div>
                                        <span class="text-stone-400 block">Mã đơn hàng đối chiếu:</span>
                                        @if($txOrder)
                                            <strong class="font-mono text-stone-900">{{ $txOrder->order_code }} (#ORDER-{{ $txOrder->id }})</strong>
                                            <span class="block text-[11px] text-stone-500">Cần thu: {{ number_format($txOrder->total_amount, 0, ',', '.') }}₫</span>
                                        @else
                                            <strong class="text-amber-700">Chưa gắn đơn hàng</strong>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-stone-400 block">Khách hàng:</span>
                                        @if($txOrder)
                                            <strong class="text-stone-800">{{ $txOrder->customer_name }}</strong>
                                            <span class="block text-[11px] text-stone-500">SĐT: {{ $txOrder->phone }}</span>
                                        @else
                                            <span class="text-stone-400">—</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-stone-400 block">Thông tin đối soát:</span>
                                        @if($tx->reconciled_by && $tx->reconciler)
                                            <strong class="text-emerald-700">Admin: {{ $tx->reconciler->name }}</strong>
                                            <span class="block text-[11px] text-stone-500">Lý do: {{ $tx->reconciliation_note }} ({{ $tx->reconciled_at?->format('d/m/Y H:i') }})</span>
                                        @elseif($isPaidTx)
                                            <strong class="text-emerald-700">Tự động qua SePay Webhook</strong>
                                        @else
                                            <strong class="text-amber-700">Đang chờ Admin kiểm tra</strong>
                                        @endif
                                    </div>
                                </div>

                                <!-- Form Đối Soát Thủ Công dành cho các giao dịch chưa khớp / thiếu tiền / thừa tiền / đến muộn -->
                                @if($needsRec && (!$txOrder || !$txOrder->isPaid()))
                                    <form action="{{ route('admin.payments.reconcile', $tx) }}" method="POST" class="bg-amber-50/90 border border-amber-300 rounded-xl p-4 space-y-3 text-xs">
                                        @csrf
                                        <p class="font-bold text-amber-900">
                                            🛠️ Xác Nhận &amp; Đối Soát Thủ Công Giao Dịch (Ghi nhận Admin thực hiện &amp; lý do)
                                        </p>
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                            <div class="md:col-span-4">
                                                <label class="block text-[11px] font-bold text-stone-700 mb-1">Chọn đơn hàng cần xác nhận PAID *</label>
                                                <select name="order_id" required class="w-full px-3 py-2 rounded-xl border border-amber-300 bg-white text-xs font-semibold">
                                                    @if($txOrder && !$txOrder->isPaid())
                                                        <option value="{{ $txOrder->id }}" selected>
                                                            {{ $txOrder->order_code }} — {{ $txOrder->customer_name }} ({{ number_format($txOrder->total_amount, 0, ',', '.') }}₫)
                                                        </option>
                                                    @else
                                                        <option value="">-- Chọn đơn hàng chưa thanh toán --</option>
                                                    @endif
                                                    @foreach(($unpaidQrOrders ?? collect()) as $uOrd)
                                                        @if(!$txOrder || $uOrd->id !== $txOrder->id)
                                                            <option value="{{ $uOrd->id }}">
                                                                {{ $uOrd->order_code }} (#ORDER-{{ $uOrd->id }}) — {{ $uOrd->customer_name }} — {{ number_format($uOrd->total_amount, 0, ',', '.') }}₫
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="md:col-span-5">
                                                <label class="block text-[11px] font-bold text-stone-700 mb-1">Lý do xác nhận thủ công (Bắt buộc) *</label>
                                                <input type="text" name="reason" required minlength="5"
                                                       placeholder="VD: Khách ghi nhầm nội dung CK, đã kiểm tra sao kê VCB khớp số tiền..."
                                                       class="w-full px-3 py-2 rounded-xl border border-amber-300 bg-white text-xs">
                                            </div>
                                            <div class="md:col-span-3">
                                                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition cursor-pointer">
                                                    ✓ Xác Nhận Đối Soát
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @endif

                                <!-- Chi tiết Payload JSON & Lịch sử thay đổi trạng thái -->
                                <details class="text-xs bg-stone-50 rounded-xl border border-stone-200 px-4 py-2.5">
                                    <summary class="font-semibold text-stone-700 cursor-pointer">
                                        Xem chi tiết Raw Payload (JSONB) &amp; Lịch sử trạng thái thanh toán
                                    </summary>
                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="min-w-0">
                                            <span class="text-[11px] font-bold text-stone-500 uppercase block mb-1">Raw Webhook Payload:</span>
                                            <pre class="bg-stone-900 text-emerald-300 p-3 rounded-xl overflow-x-auto whitespace-pre-wrap break-words max-w-full text-[11px] font-mono">{{ json_encode($tx->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                        <div>
                                            <span class="text-[11px] font-bold text-stone-500 uppercase block mb-1">Lịch sử thay đổi trạng thái (Status History):</span>
                                            @if($txPayment && !empty($txPayment->status_history))
                                                <div class="space-y-1.5">
                                                    @foreach($txPayment->status_history as $hItem)
                                                        <div class="bg-white p-2.5 rounded-lg border border-stone-200 text-[11px]">
                                                            <div class="flex justify-between font-bold text-stone-800">
                                                                <span>{{ $hItem['from_status'] ?? '' }} → {{ $hItem['to_status'] ?? '' }}</span>
                                                                <span class="text-stone-400">{{ $hItem['changed_at'] ?? '' }}</span>
                                                            </div>
                                                            <p class="text-stone-600 mt-0.5">{{ $hItem['reason'] ?? '' }}</p>
                                                            <span class="text-[10px] text-sage-700 font-semibold">Thực hiện bởi: {{ $hItem['actor_name'] ?? 'System' }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-stone-400 italic">Chưa có lịch sử trạng thái đơn hàng.</p>
                                            @endif
                                        </div>
                                    </div>
                                </details>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        @endif

        @if($activePage === 'reviews')
        <!-- ========================================================= -->
        <!-- 6. QUẢN LÝ BÌNH LUẬN & ĐÁNH GIÁ SẢN PHẨM -->
        <!-- ========================================================= -->
        <section id="section-reviews" class="min-w-0 bg-white rounded-3xl border border-stone-200/80 p-5 sm:p-6 shadow-sm space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-stone-100 pb-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-sage-600">Quản lý đánh giá khách hàng</p>
                    <h2 class="text-xl font-bold text-stone-900 mt-0.5">
                        Bình Luận &amp; Đánh Giá Sản Phẩm
                        <span class="text-sm font-normal text-stone-400">({{ $reviews->count() }}/{{ $allReviews->count() }})</span>
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 font-bold">
                        ★ TB: {{ $allReviews->count() > 0 ? number_format((float) $allReviews->avg('rating'), 1) : '0.0' }}/5
                    </span>
                    <span class="px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">
                        ✓ Đã Mua: {{ $allReviews->where('is_verified_purchase', true)->count() }}
                    </span>
                </div>
            </div>

            {{-- Filter Bar --}}
            <form action="{{ route('admin.reviews.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                <div class="sm:col-span-5">
                    <input type="text"
                           name="review_search"
                           value="{{ $reviewSearch }}"
                           placeholder="Tìm theo tên khách hàng, tên sản phẩm, nội dung..."
                           class="w-full px-3.5 py-2 text-xs rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sage-300">
                </div>
                <div class="sm:col-span-3">
                    <select name="review_rating" class="w-full px-3 py-2 text-xs rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                        <option value="ALL" {{ $reviewRating === 'ALL' ? 'selected' : '' }}>Tất cả số sao</option>
                        @for($star = 5; $star >= 1; $star--)
                            <option value="{{ $star }}" {{ (string) $reviewRating === (string) $star ? 'selected' : '' }}>{{ $star }} sao</option>
                        @endfor
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <select name="review_verified" class="w-full px-3 py-2 text-xs rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                        <option value="ALL" {{ $reviewVerified === 'ALL' ? 'selected' : '' }}>Mọi người dùng</option>
                        <option value="VERIFIED" {{ $reviewVerified === 'VERIFIED' ? 'selected' : '' }}>✓ Đã Mua</option>
                        <option value="UNVERIFIED" {{ $reviewVerified === 'UNVERIFIED' ? 'selected' : '' }}>Chưa mua</option>
                    </select>
                </div>
                <div class="sm:col-span-2 flex gap-1.5">
                    <button type="submit" class="flex-1 bg-stone-900 hover:bg-sage-600 text-white text-xs font-semibold px-3 py-2 rounded-2xl transition cursor-pointer">
                        Lọc
                    </button>
                    @if($reviewSearch !== '' || $reviewRating !== 'ALL' || $reviewVerified !== 'ALL')
                        <a href="{{ route('admin.reviews.index') }}" class="px-3 py-2 text-xs font-semibold rounded-2xl border border-stone-200 bg-white hover:bg-stone-50 text-stone-600">
                            Đặt lại
                        </a>
                    @endif
                </div>
            </form>

            {{-- Reviews List --}}
            <div class="space-y-3">
                @forelse($reviews as $rev)
                    <div class="p-4 rounded-2xl border border-stone-200/80 bg-stone-50/50 hover:border-sage-300 transition space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div class="flex items-start gap-3 min-w-0">
                                @if($rev->product)
                                    <img src="{{ $rev->product->first_image }}" alt="{{ $rev->product->name }}" class="w-12 h-12 rounded-xl object-cover bg-white border border-stone-200 flex-shrink-0">
                                @endif
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-sm text-stone-900">{{ $rev->user->name ?? 'Khách hàng' }}</span>
                                        @if($rev->is_verified_purchase)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                                ✓ Đã Mua
                                            </span>
                                        @else
                                            <span class="text-[10px] font-semibold text-stone-500 bg-stone-200/70 px-2 py-0.5 rounded-full">
                                                Chưa mua
                                            </span>
                                        @endif
                                        <span class="text-[11px] font-bold text-amber-500">
                                            {{ str_repeat('★', (int) $rev->rating) }}{{ str_repeat('☆', 5 - (int) $rev->rating) }}
                                        </span>
                                        <span class="text-[11px] text-stone-400">• {{ $rev->created_at?->format('d/m/Y H:i') }}</span>
                                    </div>

                                    <p class="text-[11px] text-stone-500 mt-0.5">
                                        {{ $rev->user->email ?? '' }}
                                        @if(!empty($rev->user?->phone))
                                            • {{ $rev->user->phone }}
                                        @endif
                                    </p>

                                    @if($rev->product)
                                        <a href="{{ route('products.show', $rev->product->slug) }}#product-reviews" target="_blank" class="inline-block text-xs font-semibold text-sage-600 hover:underline mt-1">
                                            💄 {{ $rev->product->name }} ↗
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <form action="{{ route('admin.reviews.destroy', $rev) }}" method="POST"
                                  data-confirm="true"
                                  data-confirm-type="danger"
                                  data-confirm-title="Xác nhận xóa bình luận"
                                  data-confirm-item="{{ ($rev->user->name ?? 'Khách hàng') . ' • ' . ($rev->product->name ?? '') }}"
                                  data-confirm-message="Bạn có chắc chắn muốn xóa bình luận này khỏi hệ thống?"
                                  data-confirm-detail="Bình luận sau khi xóa sẽ không thể khôi phục."
                                  data-confirm-btn="Xóa bình luận"
                                  class="flex-shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                                    Xóa
                                </button>
                            </form>
                        </div>

                        <p class="text-xs sm:text-sm text-stone-700 bg-white rounded-xl border border-stone-200/70 px-3.5 py-2.5 whitespace-pre-line">{{ $rev->comment }}</p>
                    </div>
                @empty
                    <div class="text-center py-12 text-xs text-stone-400">
                        Chưa có bình luận nào khớp với bộ lọc.
                    </div>
                @endforelse
            </div>
        </section>
        @endif
        </div>
        </main>
    </div>

    <script>
        function toggleAdminMobileSidebar(forceOpen) {
            const sidebar = document.getElementById('admin-mobile-sidebar');
            const backdrop = document.getElementById('admin-sidebar-backdrop');
            if (!sidebar) return;

            const isHidden = sidebar.classList.contains('hidden');
            const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : isHidden;

            sidebar.classList.toggle('hidden', !shouldOpen);
            if (backdrop) {
                backdrop.classList.toggle('hidden', !shouldOpen);
            }
            if (window.innerWidth < 1024) {
                document.body.classList.toggle('overflow-hidden', shouldOpen);
            }
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                toggleAdminMobileSidebar(false);
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                const sidebar = document.getElementById('admin-mobile-sidebar');
                const backdrop = document.getElementById('admin-sidebar-backdrop');
                if (sidebar) sidebar.classList.add('hidden');
                if (backdrop) backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });

        const initialProductImage = @json($editingProduct ? $editingProduct->first_image : '');

        function previewSelectedProductImage(input) {
            const previewWrap = document.getElementById('product-image-preview-wrap');
            const previewImg = document.getElementById('product-image-preview');
            const filenameEl = document.getElementById('product-image-filename');
            const clearBtn = document.getElementById('clear-selected-image-btn');
            const removeCheckbox = document.getElementById('remove-existing-image');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewWrap.classList.remove('hidden');
                    previewWrap.classList.add('block');
                };
                reader.readAsDataURL(file);
                filenameEl.textContent = 'Đã chọn: ' + file.name;
                clearBtn.classList.remove('hidden');
                if (removeCheckbox) {
                    removeCheckbox.checked = false;
                }
            }
        }

        function clearSelectedProductImage() {
            const input = document.getElementById('product-image-input');
            const previewWrap = document.getElementById('product-image-preview-wrap');
            const previewImg = document.getElementById('product-image-preview');
            const filenameEl = document.getElementById('product-image-filename');
            const clearBtn = document.getElementById('clear-selected-image-btn');

            input.value = '';
            clearBtn.classList.add('hidden');

            if (initialProductImage) {
                previewImg.src = initialProductImage;
                previewWrap.classList.remove('hidden');
                previewWrap.classList.add('block');
                filenameEl.textContent = 'Đang dùng ảnh hiện tại (Chọn tệp mới để thay thế)';
            } else {
                previewImg.src = '';
                previewWrap.classList.add('hidden');
                previewWrap.classList.remove('block');
                filenameEl.textContent = 'Hỗ trợ JPG, PNG, WEBP, GIF (Tối đa 5MB)';
            }
        }

        function toggleRemoveExistingImage(checkbox) {
            const previewWrap = document.getElementById('product-image-preview-wrap');
            const filenameEl = document.getElementById('product-image-filename');
            const input = document.getElementById('product-image-input');
            const clearBtn = document.getElementById('clear-selected-image-btn');

            if (checkbox.checked) {
                input.value = '';
                clearBtn.classList.add('hidden');
                previewWrap.classList.add('hidden');
                previewWrap.classList.remove('block');
                filenameEl.textContent = 'Ảnh hiện tại sẽ được xóa khi bấm Lưu cập nhật';
            } else if (initialProductImage) {
                previewWrap.classList.remove('hidden');
                previewWrap.classList.add('block');
                filenameEl.textContent = 'Đang dùng ảnh hiện tại (Chọn tệp mới để thay thế)';
            }
        }

        // Rich Text Editor Functions for Product Description
        function syncDescEditor() {
            const editor = document.getElementById('desc-editor');
            const hiddenInput = document.getElementById('description-hidden-input');
            if (!editor || !hiddenInput) return;
            hiddenInput.value = editor.innerHTML.trim();
        }

        function execDescCmd(command, value = null) {
            const editor = document.getElementById('desc-editor');
            if (!editor) return;
            editor.focus();
            document.execCommand(command, false, value);
            syncDescEditor();
        }

        async function uploadDescEditorImages(fileList) {
            const editor = document.getElementById('desc-editor');
            const statusEl = document.getElementById('desc-upload-status');
            const fileInput = document.getElementById('desc-editor-image-input');
            if (!editor || !fileList || fileList.length === 0) return;

            if (statusEl) statusEl.classList.remove('hidden');

            for (const file of Array.from(fileList)) {
                if (!file.type.startsWith('image/')) continue;

                const formData = new FormData();
                formData.append('image', file);
                formData.append('_token', @json(csrf_token()));

                try {
                    const res = await fetch(@json(route('admin.products.upload-editor-image')), {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: formData
                    });
                    const data = await res.json();
                    if (res.ok && data.url) {
                        editor.focus();
                        const imgHtml = `<p><img src="${data.url}" alt="Ảnh mô tả sản phẩm" class="rounded-2xl my-2 max-w-full h-auto shadow-sm border border-sage-100"></p>`;
                        document.execCommand('insertHTML', false, imgHtml);
                        syncDescEditor();
                    }
                } catch (err) {
                    console.error('Upload editor image failed:', err);
                }
            }

            if (statusEl) statusEl.classList.add('hidden');
            if (fileInput) fileInput.value = '';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const editor = document.getElementById('desc-editor');
            if (!editor) return;

            const form = editor.closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    syncDescEditor();
                });
            }

            // Support pasting images directly from clipboard
            editor.addEventListener('paste', function (e) {
                const items = e.clipboardData && e.clipboardData.items;
                if (!items) return;
                const imageFiles = [];
                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') !== -1) {
                        const f = items[i].getAsFile();
                        if (f) imageFiles.push(f);
                    }
                }
                if (imageFiles.length > 0) {
                    e.preventDefault();
                    uploadDescEditorImages(imageFiles);
                }
            });

            // Support drag & drop images directly into editor
            editor.addEventListener('drop', function (e) {
                const files = e.dataTransfer && e.dataTransfer.files;
                if (files && files.length > 0) {
                    e.preventDefault();
                    uploadDescEditorImages(files);
                }
            });
        });

        // Smooth Hash Navigation (#section-coupons, #section-products, etc.)
        function scrollToAdminHash() {
            const hash = window.location.hash;
            if (!hash || !hash.startsWith('#')) return;
            const targetEl = document.getElementById(hash.slice(1));
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
        window.addEventListener('DOMContentLoaded', scrollToAdminHash);
        window.addEventListener('hashchange', scrollToAdminHash);
    </script>
    @include('partials.confirm-modal')
</body>
</html>
