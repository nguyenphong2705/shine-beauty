@extends('layouts.app')

@section('title', $post['title'] . ' | Blog Làm Đẹp SHINE Beauty')
@section('meta_description', $post['excerpt'])
@section('canonical', route('blog.show', $post['slug']))
@section('og_type', 'article')
@section('og_image', $post['image'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-8 sm:space-y-12">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs text-stone-500">
        <a href="{{ route('home') }}" class="hover:text-sage-600">Trang chủ</a>
        <span>/</span>
        <a href="{{ route('blog.index') }}" class="hover:text-sage-600 font-medium">Blog Mỹ Phẩm &amp; Chăm Sóc Da</a>
        <span>/</span>
        <a href="{{ route('blog.index', ['topic' => $post['category_slug']]) }}" class="hover:text-sage-600">{{ $post['category'] }}</a>
        <span>/</span>
        <span class="text-stone-800 font-semibold line-clamp-1">{{ $post['title'] }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        <!-- Bài viết chính -->
        <article class="lg:col-span-8 bg-white rounded-3xl border border-sage-100 p-4 sm:p-10 shadow-xs space-y-6 sm:space-y-8">
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('blog.index', ['topic' => $post['category_slug']]) }}"
                       class="bg-sage-50 text-sage-700 border border-sage-200 text-xs font-bold px-3.5 py-1 rounded-full">
                        {{ $post['category'] }}
                    </a>
                    <span class="bg-stone-900 text-white text-xs font-semibold px-3 py-1 rounded-full">
                        Phù hợp: {{ $post['skin_type'] }}
                    </span>
                </div>

                <h1 class="font-serif text-xl sm:text-4xl font-bold text-stone-900 leading-tight">
                    {{ $post['title'] }}
                </h1>

                <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4 pt-2 pb-4 border-b border-sage-100 text-xs text-stone-500">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-sage-500 text-white font-serif font-bold flex items-center justify-center text-sm flex-shrink-0">
                            {{ mb_substr($post['author'], 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-stone-800">{{ $post['author'] }}</p>
                            <p class="text-[11px] text-stone-400">{{ $post['author_role'] }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 font-medium">
                        <span>📅 Ngày đăng: {{ $post['published_at'] }}</span>
                        <span>•</span>
                        <span>⏱ {{ $post['read_time'] }}</span>
                        <span>•</span>
                        <span>👁 {{ number_format($post['views'] ?? 0, 0, ',', '.') }} lượt xem</span>
                        <span>•</span>
                        <span>💬 {{ $post['comments_count'] ?? 0 }} bình luận</span>
                    </div>
                </div>
            </div>

            <!-- Ảnh bìa bài viết -->
            <div class="rounded-2xl sm:rounded-3xl overflow-hidden border border-sage-100 bg-sage-50">
                <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" class="w-full h-56 sm:h-96 object-cover" onerror="this.onerror=null;this.src='{{ \App\Http\Controllers\BlogController::FALLBACK_BLOG_IMAGE }}';">
            </div>

            <!-- Tóm tắt cốt lõi (Key Takeaways) -->
            @if(!empty($post['key_takeaways']))
                <div class="p-5 sm:p-6 rounded-2xl bg-[#F5FAF7] border border-sage-200 space-y-3">
                    <h2 class="font-serif text-lg font-bold text-sage-800 flex items-center gap-2">
                        <span>✨ Điểm Cốt Lõi Cần Nhớ (Key Takeaways)</span>
                    </h2>
                    <ul class="space-y-2 text-xs sm:text-sm text-stone-700 leading-relaxed">
                        @foreach($post['key_takeaways'] as $point)
                            <li class="flex items-start gap-2.5">
                                <span class="text-sage-600 font-bold mt-0.5">✓</span>
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Nội dung chi tiết từng phần -->
            <div class="space-y-6 text-stone-700 leading-relaxed text-sm sm:text-base">
                <p class="font-medium text-stone-800 text-base sm:text-lg leading-relaxed">
                    {{ $post['excerpt'] }}
                </p>

                @foreach($post['sections'] as $sec)
                    <div class="space-y-2.5 pt-2">
                        <h2 class="font-serif text-xl sm:text-2xl font-bold text-stone-900">
                            {{ $sec['heading'] }}
                        </h2>
                        <p class="text-stone-600 leading-relaxed">
                            {{ $sec['content'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            <!-- Tags & Chia sẻ -->
            <div class="pt-6 border-t border-sage-100 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-stone-400">Từ khóa:</span>
                    @foreach($post['tags'] as $tag)
                        <a href="{{ route('blog.index', ['search' => $tag]) }}"
                           class="text-xs font-semibold bg-[#F5FAF7] hover:bg-sage-100 text-sage-700 border border-sage-200 px-3 py-1 rounded-full transition">
                            #{{ $tag }}
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('blog.index') }}" class="text-xs font-bold text-sage-600 hover:underline">
                    ← Quay lại danh mục Blog
                </a>
            </div>
        </article>

        <!-- Sidebar phải: Chuyên mục Blog + Sản phẩm gợi ý trong bài -->
        <aside class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-3xl border border-sage-100 p-6 shadow-xs space-y-4">
                <h3 class="font-serif text-lg font-bold text-stone-800 pb-2 border-b border-sage-100">
                    📂 Chuyên Mục Blog Làm Đẹp
                </h3>
                <div class="space-y-2 text-xs font-semibold">
                    @foreach($topics as $tSlug => $tName)
                        <a href="{{ route('blog.index', ['topic' => $tSlug]) }}"
                           class="flex items-center justify-between p-3 rounded-2xl transition {{ $post['category_slug'] === $tSlug ? 'bg-sage-500 text-white' : 'bg-[#F5FAF7] text-stone-700 hover:bg-sage-50' }}">
                            <span>{{ $tName }}</span>
                            <span>→</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Sản phẩm mỹ phẩm gợi ý theo chủ đề bài viết -->
            <div class="bg-white rounded-3xl border border-sage-100 p-6 shadow-xs space-y-4">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-sage-500">Sản Phẩm Trong Bài Viết</span>
                    <h3 class="font-serif text-lg font-bold text-stone-800">
                        💄 Mỹ Phẩm Khuyên Dùng
                    </h3>
                </div>

                <div class="space-y-3.5">
                    @foreach($relatedProducts as $prod)
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-[#F5FAF7] border border-sage-100 hover:border-sage-300 transition">
                            <img src="{{ $prod->first_image }}" alt="{{ $prod->name }}" class="w-16 h-16 rounded-xl object-cover bg-white flex-shrink-0">
                            <div class="min-w-0 flex-1 space-y-1">
                                <span class="text-[10px] font-bold uppercase text-sage-600 block">{{ $prod->brand->name ?? 'SHINE' }} • {{ $prod->skin_type }}</span>
                                <a href="{{ route('products.show', $prod->slug) }}" class="text-xs font-bold text-stone-800 hover:text-sage-600 line-clamp-2 block">
                                    {{ $prod->name }}
                                </a>
                                <div class="flex items-center justify-between pt-0.5">
                                    <span class="text-xs font-bold text-sage-600">{{ number_format($prod->effective_price, 0, ',', '.') }}₫</span>
                                    <form action="{{ route('cart.add') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $prod->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="text-[10px] font-bold bg-stone-900 hover:bg-sage-600 text-white px-2.5 py-1 rounded-full transition cursor-pointer">
                                            + Giỏ hàng
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('products.index', ['category' => $post['related_category_slug'] ?? 'cham-soc-da']) }}"
                   class="block text-center text-xs font-bold bg-sage-50 hover:bg-sage-100 text-sage-700 border border-sage-200 py-2.5 rounded-2xl transition">
                    Xem thêm sản phẩm liên quan →
                </a>
            </div>
        </aside>
    </div>

    <!-- Bài viết liên quan -->
    @if($relatedPosts->isNotEmpty())
        <section class="pt-6 border-t border-sage-200/70 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="font-serif text-2xl font-bold text-stone-800">
                    Bài Viết Chăm Sóc Da Liên Quan
                </h2>
                <a href="{{ route('blog.index') }}" class="text-xs font-bold text-sage-600 hover:underline">Xem tất cả →</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($relatedPosts as $rPost)
                    <a href="{{ route('blog.show', $rPost['slug']) }}" class="group bg-white rounded-3xl border border-sage-100 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between">
                        <div class="h-44 overflow-hidden bg-sage-50">
                            <img src="{{ $rPost['image'] }}" alt="{{ $rPost['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.onerror=null;this.src='{{ \App\Http\Controllers\BlogController::FALLBACK_BLOG_IMAGE }}';">
                        </div>
                        <div class="p-5 space-y-2 flex-1 flex flex-col justify-between">
                            <div class="space-y-1.5">
                                <span class="text-[10px] font-bold uppercase text-sage-600">{{ $rPost['category'] }}</span>
                                <h3 class="font-serif text-base font-bold text-stone-800 group-hover:text-sage-600 line-clamp-2">
                                    {{ $rPost['title'] }}
                                </h3>
                            </div>
                            <p class="text-[11px] text-stone-400 pt-2">
                                {{ $rPost['author'] }} • {{ $rPost['read_time'] }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
