@extends('layouts.app')

@section('title', 'SHINE Beauty | Mỹ Phẩm & Chăm Sóc Da Cao Cấp Chính Hãng')
@section('meta_description', 'SHINE Beauty — Hệ thống phân phối mỹ phẩm cao cấp & chăm sóc da chuẩn khoa học chính hãng từ Pháp, Hàn Quốc và Nhật Bản. Miễn phí vận chuyển toàn quốc cho đơn từ 500.000₫.')
@section('canonical', route('home'))

@section('content')
<div class="space-y-12 sm:space-y-20">
    <!-- 1. HERO BANNER -->
    <section class="relative overflow-hidden bg-gradient-to-br from-sage-100/80 via-[#F5FAF7] to-[#ECF5F0] border-b border-sage-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                <!-- Left Column -->
                <div class="lg:col-span-7 space-y-5 sm:space-y-6">
                    <div class="inline-flex items-center gap-2 bg-white/90 border border-sage-200 text-sage-600 px-3.5 sm:px-4 py-1.5 rounded-full text-[11px] sm:text-xs font-semibold tracking-wide shadow-2xs max-w-full">
                        <span class="truncate">✦ BỘ SƯU TẬP DƯỠNG DA 2026 — GLOW & RADIANCE</span>
                    </div>

                    <h1 class="font-serif text-3xl sm:text-5xl lg:text-6xl font-bold text-stone-900 leading-[1.15]">
                        Đánh Thức Vẻ Đẹp <br>
                        <span class="text-sage-500 italic font-normal">Thuần Khiết & Rạng Ngời</span>
                    </h1>

                    <p class="text-stone-600 text-sm sm:text-lg leading-relaxed max-w-2xl">
                        Khám phá quy trình chăm sóc da chuẩn khoa học với Niacinamide 10%, BHA 2%, Retinol 0.5% tinh khiết và Hyaluronic Acid đa tầng từ những thương hiệu danh tiếng hàng đầu Pháp, Hàn Quốc và Nhật Bản.
                    </p>

                    <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 sm:gap-4 pt-2">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center gap-2.5 bg-sage-500 hover:bg-sage-600 text-white font-semibold px-6 sm:px-8 py-3.5 sm:py-4 rounded-full shadow-lg shadow-sage-200/90 transition transform hover:-translate-y-0.5 text-sm sm:text-base">
                            <span>Khám Phá Bộ Sưu Tập →</span>
                        </a>
                        <a href="#skin-explorer" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-sage-50 text-stone-800 font-semibold px-6 sm:px-7 py-3.5 sm:py-4 rounded-full border border-sage-200 transition text-sm sm:text-base">
                            <span>Chọn Mỹ Phẩm Theo Loại Da</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 pt-5 sm:pt-6 border-t border-sage-200/60 max-w-lg text-xs text-stone-600">
                        <div>✓ 100% Chính hãng</div>
                        <div>✓ Kiểm nghiệm da liễu</div>
                        <div>✓ Đổi trả 14 ngày</div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="lg:col-span-5 relative">
                    <div class="relative rounded-3xl sm:rounded-[2.5rem] overflow-hidden shadow-2xl shadow-sage-200/80 border-4 sm:border-8 border-white aspect-[4/5] max-w-md mx-auto lg:max-w-none">
                        <img src="https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=1000&q=85" alt="SHINE Beauty Skincare Collection" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-900/50 via-transparent to-transparent flex items-end p-4 sm:p-8">
                            <div class="bg-white/95 backdrop-blur-md p-4 sm:p-5 rounded-2xl w-full shadow-lg">
                                <span class="inline-flex items-center gap-1 bg-[#D4728C] text-white text-[10px] sm:text-[11px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full shadow-2xs border border-[#C25E79]">✦ Best Seller Tuần Này</span>
                                <p class="font-serif font-bold text-stone-800 text-sm sm:text-base mt-1.5">Serum Hyaluronic Acid 2% + B5 Cấp Ẩm Đa Tầng</p>
                                <p class="text-xs text-stone-500 mt-1">Phục hồi màng ẩm tự nhiên chỉ sau 7 ngày</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. DANH MỤC NỔI BẬT -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-6 sm:mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-sage-500">Danh Mục Làm Đẹp</span>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold text-stone-800 mt-1">Khám Phá Theo Danh Mục</h2>
            </div>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-sage-600 hover:text-sage-700 mt-2 sm:mt-0">
                Xem tất cả danh mục →
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-6">
            @foreach($categories as $cat)
                <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="group relative h-48 sm:h-72 rounded-2xl sm:rounded-3xl overflow-hidden shadow-sm border border-sage-100">
                    <img src="{{ $cat->image }}" alt="{{ $cat->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-stone-950/75 via-stone-900/20 to-transparent flex flex-col justify-end p-3.5 sm:p-6">
                        <h3 class="font-serif text-base sm:text-2xl font-bold text-white group-hover:text-sage-200 transition leading-snug">{{ $cat->name }}</h3>
                        <span class="text-[11px] sm:text-xs font-medium text-sage-200 mt-1">Khám phá ({{ $cat->products_count }} SP) →</span>
                    </div>
                </a>
            @endforeach

            <a href="{{ route('blog.index') }}" class="group relative h-48 sm:h-72 rounded-2xl sm:rounded-3xl overflow-hidden shadow-sm border border-sage-100 col-span-2 sm:col-span-1">
                <img src="https://images.unsplash.com/photo-1617897903246-719242758050?auto=format&fit=crop&w=900&q=80" alt="Blog Mỹ Phẩm & Chăm Sóc Da" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" onerror="this.onerror=null;this.src='{{ \App\Http\Controllers\BlogController::FALLBACK_BLOG_IMAGE }}';">
                <div class="absolute inset-0 bg-gradient-to-t from-stone-950/80 via-stone-900/30 to-transparent flex flex-col justify-end p-3.5 sm:p-6">
                    <span class="inline-block w-fit bg-sage-500 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full mb-1.5">Cẩm Nang</span>
                    <h3 class="font-serif text-base sm:text-2xl font-bold text-white group-hover:text-sage-200 transition leading-snug">Blog Chăm Sóc Da</h3>
                    <span class="text-[11px] sm:text-xs font-medium text-sage-200 mt-1">Kiến thức mỹ phẩm &amp; da liễu →</span>
                </div>
            </a>
        </div>
    </section>

    <!-- 3. SẢN PHẨM BÁN CHẠY (BEST SELLERS) -->
    <section class="bg-gradient-to-b from-sage-50/60 to-[#F5FAF7] py-12 sm:py-16 border-y border-sage-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-6 sm:mb-10">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-sage-500">Được Yêu Thích Nhất</span>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-stone-800 mt-1">Sản Phẩm Bán Chạy (Best Sellers)</h2>
                </div>
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-sage-600 hover:text-sage-700 mt-2 sm:mt-0">
                    Xem toàn bộ {{ $totalProductsCount ?? 0 }} sản phẩm →
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5 sm:gap-7">
                @foreach($bestSellers as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>

    <!-- 4. LỌC NHANH THEO LOẠI DA & DANH SÁCH SẢN PHẨM PHÂN TRANG -->
    <section id="skin-explorer" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24">
        <div class="text-center max-w-2xl mx-auto mb-6 sm:mb-10">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-sage-500">Cá Nhân Hóa Chu Trình Dưỡng Da</span>
            <h2 class="font-serif text-2xl sm:text-3xl font-bold text-stone-800 mt-1">Chọn Mỹ Phẩm Theo Loại Da Của Bạn</h2>
            <p class="text-xs sm:text-sm text-stone-500 mt-2">
                Mỗi làn da có một nhu cầu riêng biệt. Hãy chọn tình trạng da để SHINE Beauty gợi ý công thức hoạt chất phù hợp nhất.
            </p>
        </div>

        @php
            $skinTabs = [
                ['key' => 'Tất cả', 'label' => 'Tất cả loại da', 'desc' => 'Bộ sưu tập chăm sóc toàn diện'],
                ['key' => 'Da dầu', 'label' => 'Da dầu & Hỗn hợp', 'desc' => 'Kiểm soát bã nhờn, thu nhỏ lỗ chân lông (Niacinamide, BHA)'],
                ['key' => 'Da khô', 'label' => 'Da khô & Thiếu ẩm', 'desc' => 'Cấp ẩm đa tầng, khóa ẩm chuyên sâu (Hyaluronic Acid, Nhân sâm)'],
                ['key' => 'Da nhạy cảm', 'label' => 'Da nhạy cảm', 'desc' => 'Làm dịu kích ứng, phục hồi màng ẩm (Vitamin B5, Rau má)'],
            ];
        @endphp

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
            @foreach($skinTabs as $tab)
                @php $active = ($selectedSkin === $tab['key']); @endphp
                <a href="{{ route('home', ['skin_type' => $tab['key']]) }}#skin-explorer"
                   class="text-left p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border transition-all duration-200 block {{ $active ? 'bg-sage-500 text-white border-sage-500 shadow-lg shadow-sage-200' : 'bg-white hover:bg-sage-50/60 text-stone-800 border-sage-100' }}">
                    <span class="font-serif font-bold text-sm sm:text-base block mb-1">{{ $tab['label'] }}</span>
                    <p class="text-[11px] sm:text-xs leading-relaxed {{ $active ? 'text-sage-50' : 'text-stone-500' }}">{{ $tab['desc'] }}</p>
                </a>
            @endforeach
        </div>

        @if($skinProducts->isEmpty())
            <div class="bg-white rounded-3xl border border-sage-100 p-8 sm:p-12 text-center space-y-3">
                <p class="font-serif text-lg font-bold text-stone-700">Chưa có sản phẩm phù hợp</p>
                <p class="text-sm text-stone-500">Vui lòng chọn loại da khác hoặc xem lại tất cả sản phẩm.</p>
                <a href="{{ route('home', ['skin_type' => 'Tất cả']) }}#skin-explorer" class="inline-block bg-sage-500 hover:bg-sage-600 text-white text-xs font-semibold px-5 py-2.5 rounded-full transition">
                    Xem tất cả sản phẩm
                </a>
            </div>
        @else
            <!-- Thông tin số lượng sản phẩm -->
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-stone-500 mb-4 px-1">
                <span>Hiển thị {{ $skinProducts->firstItem() ?? 0 }} - {{ $skinProducts->lastItem() ?? 0 }} trong tổng số <strong class="text-sage-700">{{ $skinProducts->total() }}</strong> sản phẩm</span>
                <span>Trang {{ $skinProducts->currentPage() }} / {{ $skinProducts->lastPage() }}</span>
            </div>

            <!-- Lưới hiển thị: 1 cột mobile, 2 cột phablet, 3 cột tablet, 4 cột desktop -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($skinProducts as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>

            <!-- Thanh phân trang (Pagination) -->
            @if($skinProducts->hasPages())
                <div class="pt-10 flex flex-col items-center gap-3">
                    <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="Phân trang sản phẩm">
                        {{-- Nút trang trước --}}
                        @if($skinProducts->onFirstPage())
                            <span class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs sm:text-sm font-medium rounded-full border border-stone-200 text-stone-300 cursor-not-allowed select-none bg-stone-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                <span>Trước</span>
                            </span>
                        @else
                            <a href="{{ $skinProducts->previousPageUrl() }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs sm:text-sm font-medium rounded-full border border-sage-200 text-sage-700 bg-white hover:bg-sage-50 hover:border-sage-300 transition shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                <span>Trước</span>
                            </a>
                        @endif

                        {{-- Danh sách các số trang --}}
                        @foreach($skinProducts->getUrlRange(1, $skinProducts->lastPage()) as $pageNum => $url)
                            @if($pageNum === $skinProducts->currentPage())
                                <span class="w-10 h-10 flex items-center justify-center text-xs sm:text-sm rounded-full bg-sage-500 text-white font-bold shadow-md shadow-sage-200 select-none" aria-current="page">
                                    {{ $pageNum }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="w-10 h-10 flex items-center justify-center text-xs sm:text-sm rounded-full border border-sage-200 text-stone-700 bg-white hover:bg-sage-50 hover:text-sage-700 hover:border-sage-300 transition shadow-2xs font-medium">
                                    {{ $pageNum }}
                                </a>
                            @endif
                        @endforeach

                        {{-- Nút trang sau --}}
                        @if($skinProducts->hasMorePages())
                            <a href="{{ $skinProducts->nextPageUrl() }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs sm:text-sm font-medium rounded-full border border-sage-200 text-sage-700 bg-white hover:bg-sage-50 hover:border-sage-300 transition shadow-2xs">
                                <span>Tiếp</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs sm:text-sm font-medium rounded-full border border-stone-200 text-stone-300 cursor-not-allowed select-none bg-stone-50">
                                <span>Tiếp</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        @endif
                    </nav>
                </div>
            @endif
        @endif
    </section>

    <!-- 5. THƯƠNG HIỆU ĐỒNG HÀNH -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-sage-100 p-5 sm:p-10 shadow-xs">
            <div class="text-center mb-6 sm:mb-8">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-sage-500">Đối Tác Chính Hãng</span>
                <h3 class="font-serif text-xl sm:text-2xl font-bold text-stone-800 mt-1">Thương Hiệu Mỹ Phẩm Hàng Đầu Thế Giới</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
                @foreach($brands as $brand)
                    <a href="{{ route('products.index', ['brand' => $brand->name]) }}" class="flex flex-col items-center justify-center p-4 sm:p-6 rounded-2xl bg-[#F5FAF7] hover:bg-sage-50/70 border border-sage-100/80 transition group text-center">
                        <span class="font-serif text-base sm:text-xl font-bold text-stone-800 group-hover:text-sage-600">{{ $brand->name }}</span>
                        <span class="text-[11px] sm:text-xs text-stone-400 mt-1">Xuất xứ: {{ $brand->origin }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 6. BLOG MỸ PHẨM & CHĂM SÓC DA -->
    @php
        $homeBlogPosts = array_slice(\App\Http\Controllers\BlogController::getAllPosts(), 0, 3);
    @endphp
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-6 sm:mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-sage-500">Góc Chia Sẻ Chuyên Gia</span>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold text-stone-800 mt-1">Blog Mỹ Phẩm &amp; Chăm Sóc Da</h2>
            </div>
            <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-sage-600 hover:text-sage-700 mt-2 sm:mt-0">
                Xem tất cả bài viết Blog →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-7">
            @foreach($homeBlogPosts as $bPost)
                <article class="bg-white rounded-3xl border border-sage-100 overflow-hidden shadow-xs hover:shadow-lg transition flex flex-col justify-between group">
                    <div>
                        <a href="{{ route('blog.show', $bPost['slug']) }}" class="block relative h-52 overflow-hidden bg-sage-50">
                            <img src="{{ $bPost['image'] }}" alt="{{ $bPost['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.onerror=null;this.src='{{ \App\Http\Controllers\BlogController::FALLBACK_BLOG_IMAGE }}';">
                            <span class="absolute top-3 left-3 bg-white/95 text-sage-700 border border-sage-200 text-[11px] font-bold px-3 py-1 rounded-full">
                                {{ $bPost['category'] }}
                            </span>
                        </a>
                        <div class="p-6 space-y-2.5">
                            <div class="flex items-center justify-between text-[11px] text-stone-400">
                                <span>📅 {{ $bPost['published_at'] }}</span>
                                <span>⏱ {{ $bPost['read_time'] }}</span>
                            </div>
                            <h3 class="font-serif text-lg font-bold text-stone-800 group-hover:text-sage-600 transition line-clamp-2">
                                <a href="{{ route('blog.show', $bPost['slug']) }}">{{ $bPost['title'] }}</a>
                            </h3>
                            <p class="text-xs text-stone-500 line-clamp-3 leading-relaxed">
                                {{ $bPost['excerpt'] }}
                            </p>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-sage-100 bg-[#F5FAF7]/50 flex items-center justify-between text-xs">
                        <span class="font-semibold text-stone-700">{{ $bPost['author'] }}</span>
                        <a href="{{ route('blog.show', $bPost['slug']) }}" class="font-bold text-sage-600 hover:text-sage-700">Đọc tiếp →</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
