@extends('layouts.app')

@section('title', 'Blog Mỹ Phẩm & Chăm Sóc Da Khoa Học | SHINE Beauty')
@section('meta_description', 'Cẩm nang chăm sóc da chuẩn khoa học, phân tích hoạt chất Niacinamide, BHA, Retinol, Vitamin B5 và review các sản phẩm mỹ phẩm chính hãng tại SHINE Beauty.')
@section('canonical', route('blog.index'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
    <!-- 1. THANH CHUYÊN MỤC BLOG (2/3 BÊN TRÁI) & THANH TÌM KIẾM (1/3 BÊN PHẢI, NGANG DÒNG DANH MỤC ĐẦU TIÊN) -->
    <section class="bg-white rounded-3xl border border-sage-100 p-5 sm:p-6 shadow-sm space-y-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
            <!-- Bên trái (2/3 = 8/12 cột): Danh mục Blog căn thẳng 1 hàng ngang -->
            <div class="lg:col-span-8 flex flex-wrap xl:flex-nowrap items-center gap-2">
                <span class="h-10 inline-flex items-center text-xs font-bold uppercase tracking-wider text-stone-500 mr-1 whitespace-nowrap leading-none flex-shrink-0">
                    Chuyên mục:
                </span>
                <a href="{{ route('blog.index', array_filter(['skin_type' => $selectedSkin, 'search' => $search, 'sort' => $sort !== 'newest' ? $sort : null])) }}"
                   class="h-10 px-3.5 inline-flex items-center justify-center rounded-full text-xs font-bold border leading-none whitespace-nowrap transition flex-shrink-0 {{ $selectedTopic === '' ? 'bg-sage-500 text-white border-sage-500 shadow-sm' : 'bg-[#F5FAF7] text-stone-700 border-sage-200 hover:bg-sage-50' }}">
                    Tất cả ({{ $allPostsCount }})
                </a>
                @foreach($topics as $topicSlug => $topicName)
                    <a href="{{ route('blog.index', array_filter(['topic' => $topicSlug, 'skin_type' => $selectedSkin, 'search' => $search, 'sort' => $sort !== 'newest' ? $sort : null])) }}"
                       class="h-10 px-3.5 inline-flex items-center justify-center rounded-full text-xs font-bold border leading-none whitespace-nowrap transition flex-shrink-0 {{ $selectedTopic === $topicSlug ? 'bg-sage-500 text-white border-sage-500 shadow-sm' : 'bg-[#F5FAF7] text-stone-700 border-sage-200 hover:bg-sage-50' }}">
                        {{ $topicName }}
                    </a>
                @endforeach
            </div>

            <!-- Bên phải (1/3 = 4/12 cột): Input và nút kính lúp cao bằng nhau (h-10) và thẳng hàng tuyệt đối -->
            <form action="{{ route('blog.index') }}" method="GET" class="lg:col-span-4 w-full m-0">
                @if($selectedTopic)
                    <input type="hidden" name="topic" value="{{ $selectedTopic }}">
                @endif
                @if($selectedSkin)
                    <input type="hidden" name="skin_type" value="{{ $selectedSkin }}">
                @endif
                @if($sort && $sort !== 'newest')
                    <input type="hidden" name="sort" value="{{ $sort }}">
                @endif
                <div class="flex items-center w-full h-10 rounded-full border border-sage-200 bg-[#F5FAF7] focus-within:bg-white focus-within:border-sage-500 focus-within:ring-2 focus-within:ring-sage-400/30 overflow-hidden shadow-sm transition">
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Tìm bài viết: BHA, Retinol, cấp ẩm..."
                        class="h-full flex-1 min-w-0 px-4 py-0 m-0 text-xs leading-none text-stone-700 bg-transparent border-0 outline-none focus:outline-none focus:ring-0">
                    @if($search !== '')
                        <a href="{{ route('blog.index', array_filter(['topic' => $selectedTopic, 'skin_type' => $selectedSkin, 'sort' => $sort !== 'newest' ? $sort : null])) }}"
                           title="Xóa từ khóa tìm kiếm"
                           class="h-full px-2.5 inline-flex items-center justify-center text-stone-400 hover:text-stone-600 text-xs leading-none transition flex-shrink-0">
                            ✕
                        </a>
                    @endif
                    <button type="submit"
                            aria-label="Tìm kiếm bài viết"
                            title="Tìm kiếm"
                            class="h-full w-11 bg-sage-500 hover:bg-sage-600 text-white inline-flex items-center justify-center border-0 m-0 transition cursor-pointer flex-shrink-0">
                        <svg class="w-4 h-4 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-sage-100 text-xs">
            <div class="flex flex-wrap items-center gap-2">
                <span class="h-9 inline-flex items-center font-bold text-stone-500 mr-1 whitespace-nowrap leading-none">
                    Chăm sóc theo loại da:
                </span>
                @foreach(['' => 'Tất cả loại da', 'Da dầu' => '💧 Da dầu & Lỗ chân lông to', 'Da khô' => '🌿 Da khô & Thiếu ẩm', 'Da nhạy cảm' => '🌸 Da nhạy cảm & Phục hồi'] as $skinKey => $skinLabel)
                    <a href="{{ route('blog.index', array_filter(['topic' => $selectedTopic, 'skin_type' => $skinKey, 'search' => $search, 'sort' => $sort !== 'newest' ? $sort : null])) }}"
                       class="h-9 px-3.5 inline-flex items-center justify-center rounded-full font-semibold border leading-none whitespace-nowrap transition {{ $selectedSkin === $skinKey ? 'bg-stone-900 text-white border-stone-900' : 'bg-sage-50/70 text-stone-600 border-sage-200/80 hover:bg-sage-100' }}">
                        {{ $skinLabel }}
                    </a>
                @endforeach
            </div>

            @if($selectedTopic !== '' || $selectedSkin !== '' || $search !== '' || $sort !== 'newest')
                <a href="{{ route('blog.index') }}" class="h-9 inline-flex items-center text-xs font-semibold text-terracotta-600 hover:underline whitespace-nowrap leading-none">
                    ✕ Đặt lại toàn bộ bộ lọc
                </a>
            @endif
        </div>
    </section>

    <!-- 2. TIÊU ĐỀ BLOG MỸ PHẨM & CHĂM SÓC DA (TRÁI) + BỘ LỌC SẮP XẾP SELECT-OPTION (PHẢI) -->
    <section class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-sage-100">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-sage-500">Cẩm Nang Chăm Sóc Sắc Đẹp</span>
                <h1 class="font-serif text-2xl sm:text-3xl font-bold text-stone-800 mt-0.5">
                    Blog Mỹ Phẩm &amp; Chăm Sóc Da ({{ $posts->total() }} bài viết)
                </h1>
            </div>

            <!-- Bộ lọc select - option nằm phía bên phải tiêu đề, căn thẳng hàng ngang -->
            <form action="{{ route('blog.index') }}" method="GET" class="flex items-center gap-2.5 flex-shrink-0 m-0">
                @if($selectedTopic)
                    <input type="hidden" name="topic" value="{{ $selectedTopic }}">
                @endif
                @if($selectedSkin)
                    <input type="hidden" name="skin_type" value="{{ $selectedSkin }}">
                @endif
                @if($search)
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif

                <label for="blog-sort-select" class="h-10 inline-flex items-center text-xs font-bold text-stone-600 whitespace-nowrap leading-none">
                    Lọc theo:
                </label>
                <div class="relative h-10 flex items-center">
                    <select id="blog-sort-select"
                            name="sort"
                            onchange="this.form.submit()"
                            class="h-10 appearance-none bg-white border border-sage-200 hover:border-sage-400 text-stone-700 text-xs font-bold leading-none rounded-xl pl-3.5 pr-9 py-0 shadow-sm focus:outline-none focus:ring-2 focus:ring-sage-400 focus:border-sage-500 cursor-pointer transition">
                        <option value="newest" @selected($sort === 'newest')>Ngày mới nhất</option>
                        <option value="most_viewed" @selected($sort === 'most_viewed')>Bài viết xem nhiều nhất</option>
                        <option value="most_commented" @selected($sort === 'most_commented')>Bài viết Hot (Bình luận nhiều nhất)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-stone-500">
                        <svg class="w-3.5 h-3.5 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </form>
        </div>

        @if($posts->isEmpty())
            <div class="bg-white rounded-3xl border border-sage-100 p-12 text-center space-y-3">
                <p class="font-serif text-xl font-bold text-stone-700">Không tìm thấy bài viết phù hợp</p>
                <p class="text-xs text-stone-500">Hãy thử chọn chuyên mục khác hoặc xem toàn bộ {{ $allPostsCount }} bài viết cẩm nang chăm sóc da.</p>
                <a href="{{ route('blog.index') }}" class="inline-block bg-sage-500 hover:bg-sage-600 text-white text-xs font-semibold px-6 py-2.5 rounded-full transition">
                    Xem tất cả bài viết Blog
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($posts as $post)
                    <article class="bg-white rounded-3xl border border-sage-100 overflow-hidden shadow-xs hover:shadow-lg transition flex flex-col justify-between group">
                        <div>
                            <a href="{{ route('blog.show', $post['slug']) }}" class="block relative h-48 overflow-hidden bg-sage-50">
                                <img src="{{ $post['image'] }}"
                                     alt="{{ $post['title'] }}"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ \App\Http\Controllers\BlogController::FALLBACK_BLOG_IMAGE }}';"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute top-3 left-3 right-3 flex items-start justify-between gap-1.5">
                                    <span class="bg-white/95 backdrop-blur-xs text-sage-700 border border-sage-200 text-[10px] font-bold px-2.5 py-1 rounded-full line-clamp-1">
                                        {{ $post['category'] }}
                                    </span>
                                    @if(!empty($post['is_hot']))
                                        <span class="bg-terracotta-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-2xs flex-shrink-0">
                                            🔥 HOT
                                        </span>
                                    @endif
                                </div>
                                <div class="absolute bottom-2.5 left-3">
                                    <span class="bg-stone-900/80 text-white text-[10px] font-semibold px-2.5 py-0.5 rounded-full">
                                        {{ $post['skin_type'] }}
                                    </span>
                                </div>
                            </a>

                            <div class="p-5 space-y-2.5">
                                <div class="flex flex-wrap items-center justify-between gap-1 text-[11px] text-stone-400 font-medium">
                                    <span>📅 {{ $post['published_at'] }}</span>
                                    <div class="flex items-center gap-2">
                                        <span title="Lượt xem bài viết">👁 {{ number_format($post['views'], 0, ',', '.') }}</span>
                                        <span title="Số lượt bình luận" class="{{ $sort === 'most_commented' ? 'text-terracotta-600 font-bold' : '' }}">💬 {{ $post['comments_count'] }}</span>
                                    </div>
                                </div>

                                <h2 class="font-serif text-base font-bold text-stone-800 group-hover:text-sage-600 transition leading-snug line-clamp-2">
                                    <a href="{{ route('blog.show', $post['slug']) }}">
                                        {{ $post['title'] }}
                                    </a>
                                </h2>

                                <p class="text-xs text-stone-600 leading-relaxed line-clamp-3">
                                    {{ $post['excerpt'] }}
                                </p>

                                <div class="flex flex-wrap gap-1 pt-1">
                                    @foreach(array_slice($post['tags'], 0, 3) as $tag)
                                        <span class="text-[10px] font-semibold bg-[#F5FAF7] text-sage-700 border border-sage-200/80 px-2 py-0.5 rounded-full">
                                            #{{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-3.5 border-t border-sage-100 bg-[#F5FAF7]/60 flex items-center justify-between">
                            <div class="text-[11px] min-w-0 pr-2">
                                <p class="font-bold text-stone-800 truncate">{{ $post['author'] }}</p>
                                <p class="text-[10px] text-stone-500">{{ $post['read_time'] }}</p>
                            </div>
                            <a href="{{ route('blog.show', $post['slug']) }}" class="text-xs font-bold text-sage-600 hover:text-sage-700 inline-flex items-center gap-1 flex-shrink-0">
                                <span>Đọc bài</span>
                                <span>→</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Thanh phân trang bài viết Blog (tối đa 3 hàng x 4 cột = 12 bài/trang) -->
            @if($posts->hasPages())
                <nav aria-label="Phân trang bài viết Blog" class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-sage-100">
                    <p class="text-xs text-stone-500">
                        Hiển thị <span class="font-bold text-stone-800">{{ $posts->firstItem() }}–{{ $posts->lastItem() }}</span>
                        trong tổng số <span class="font-bold text-stone-800">{{ $posts->total() }}</span> bài viết
                        (Trang {{ $posts->currentPage() }}/{{ $posts->lastPage() }})
                    </p>

                    <div class="flex items-center gap-1.5">
                        @if($posts->onFirstPage())
                            <span class="px-3.5 py-2 rounded-xl border border-sage-100 bg-stone-50 text-stone-300 text-xs font-semibold cursor-not-allowed">
                                ← Trước
                            </span>
                        @else
                            <a href="{{ $posts->previousPageUrl() }}"
                               class="px-3.5 py-2 rounded-xl border border-sage-200 bg-white text-stone-700 hover:bg-sage-50 hover:border-sage-400 text-xs font-bold transition">
                                ← Trước
                            </a>
                        @endif

                        @for($page = 1; $page <= $posts->lastPage(); $page++)
                            @if($page == $posts->currentPage())
                                <span aria-current="page"
                                      class="w-9 h-9 rounded-xl bg-sage-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $posts->url($page) }}"
                                   class="w-9 h-9 rounded-xl border border-sage-200 bg-white text-stone-700 hover:bg-sage-50 hover:border-sage-400 text-xs font-bold flex items-center justify-center transition">
                                    {{ $page }}
                                </a>
                            @endif
                        @endfor

                        @if($posts->hasMorePages())
                            <a href="{{ $posts->nextPageUrl() }}"
                               class="px-3.5 py-2 rounded-xl border border-sage-200 bg-white text-stone-700 hover:bg-sage-50 hover:border-sage-400 text-xs font-bold transition">
                                Sau →
                            </a>
                        @else
                            <span class="px-3.5 py-2 rounded-xl border border-sage-100 bg-stone-50 text-stone-300 text-xs font-semibold cursor-not-allowed">
                                Sau →
                            </span>
                        @endif
                    </div>
                </nav>
            @endif
        @endif
    </section>

    <!-- 3. SẢN PHẨM MỸ PHẨM & CHĂM SÓC DA ĐƯỢC CHUYÊN GIA KHUYÊN DÙNG -->
    @if($recommendedProducts->isNotEmpty())
        <section class="bg-gradient-to-b from-sage-50/70 to-white rounded-3xl border border-sage-100 p-6 sm:p-10 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-sage-500">Gợi Ý Từ Chuyên Gia Da Liễu</span>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-stone-800 mt-1">
                        Sản Phẩm Mỹ Phẩm &amp; Chăm Sóc Da Nổi Bật
                    </h2>
                </div>
                <a href="{{ route('products.index') }}" class="text-xs font-bold text-sage-600 hover:underline">
                    Xem toàn bộ cửa hàng mỹ phẩm →
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($recommendedProducts as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
