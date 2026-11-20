@extends('layouts.app')

@php
    $finalPrice = $product->sale_price ?? $product->price;
    $discountPercent = ($product->sale_price && $product->sale_price < $product->price)
        ? round((($product->price - $product->sale_price) / $product->price) * 100)
        : null;
    $ingredientsList = array_filter(array_map('trim', explode(',', (string) $product->ingredients)));
    $seoDescription = \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 155, '...');
@endphp

@section('title', $product->name . ' - Chính Hãng ' . ($product->brand->name ?? 'SHINE Beauty') . ' | SHINE Beauty')
@section('meta_description', $seoDescription ?: ($product->name . ' chính hãng tại SHINE Beauty. Dành cho ' . $product->skin_type . ', dung tích ' . $product->volume . '.'))
@section('meta_keywords', implode(', ', array_filter([$product->name, $product->brand->name ?? '', $product->category->name ?? '', $product->skin_type, 'mỹ phẩm chính hãng', 'SHINE Beauty'])))
@section('canonical', route('products.show', $product->slug))
@section('og_type', 'product')
@section('og_image', $product->first_image)

@push('structured_data')
<!-- Dữ liệu có cấu trúc Schema.org Product & BreadcrumbList chuẩn Google Rich Snippets -->
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@graph": [
        {
            "@@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@@type": "ListItem",
                    "position": 1,
                    "name": "Trang chủ",
                    "item": "{{ route('home') }}"
                },
                {
                    "@@type": "ListItem",
                    "position": 2,
                    "name": "{{ $product->category->name }}",
                    "item": "{{ route('products.index', ['category' => $product->category->slug]) }}"
                },
                {
                    "@@type": "ListItem",
                    "position": 3,
                    "name": "{{ $product->name }}",
                    "item": "{{ route('products.show', $product->slug) }}"
                }
            ]
        },
        {
            "@@type": "Product",
            "name": "{{ $product->name }}",
            "sku": "{{ $product->product_code }}",
            "image": ["{{ $product->first_image }}"],
            "description": "{{ addslashes($seoDescription) }}",
            "brand": {
                "@@type": "Brand",
                "name": "{{ $product->brand->name ?? 'SHINE Beauty' }}"
            },
            "offers": {
                "@@type": "Offer",
                "url": "{{ route('products.show', $product->slug) }}",
                "priceCurrency": "VND",
                "price": "{{ (int) $finalPrice }}",
                "availability": "{{ $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
                "itemCondition": "https://schema.org/NewCondition"
            },
            "aggregateRating": {
                "@@type": "AggregateRating",
                "ratingValue": "{{ number_format((float) $product->rating, 1, '.', '') }}",
                "reviewCount": "{{ max(1, (int) $product->reviews_count) }}"
            }
        }
    ]
}
</script>
@endpush

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-10 sm:space-y-16">
    <!-- Breadcrumbs -->
    <nav class="flex flex-wrap items-center gap-2 text-xs text-stone-500">
        <a href="{{ route('home') }}" class="hover:text-sage-600">Trang chủ</a>
        <span>/</span>
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-sage-600">
            {{ $product->category->name }}
        </a>
        <span>/</span>
        <span class="text-stone-800 font-medium line-clamp-1">{{ $product->name }}</span>
    </nav>

    <!-- Product Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
        <!-- Gallery -->
        <div class="lg:col-span-6 space-y-4">
            <div class="relative aspect-square rounded-3xl overflow-hidden bg-white border border-sage-100 shadow-sm max-w-lg mx-auto lg:max-w-none">
                <img id="main-product-image"
                     src="{{ $product->first_image }}"
                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=80';"
                     alt="{{ $product->name }}"
                     class="w-full h-full object-cover">
                @if($discountPercent)
                    <span class="absolute top-4 left-4 bg-terracotta-600 text-white text-xs font-bold px-3.5 py-1.5 rounded-full shadow-md border border-terracotta-500">
                        Tiết kiệm {{ $discountPercent }}%
                    </span>
                @endif
            </div>

            @if(is_array($product->images) && count($product->images) > 1)
                <div class="flex gap-3 overflow-x-auto no-scrollbar pb-1 justify-center lg:justify-start">
                    @foreach($product->images as $img)
                        <button type="button" onclick="document.getElementById('main-product-image').src='{{ $img }}'" class="w-16 h-16 sm:w-20 sm:h-20 flex-shrink-0 rounded-2xl overflow-hidden border-2 border-sage-200 hover:border-sage-500 transition">
                            <img src="{{ $img }}"
                                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=80';"
                                 alt="Thumbnail"
                                 class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Product Details -->
        <div class="lg:col-span-6 space-y-5 sm:space-y-6">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-mono font-bold bg-stone-900 text-white px-3 py-1 rounded-full">
                        Mã SP: {{ $product->product_code }}
                    </span>
                    @if($product->is_best_seller)
                        <span class="inline-flex items-center gap-1 bg-[#D4728C] text-white text-xs font-semibold px-3 py-1 rounded-full shadow-2xs border border-[#C25E79]">
                            ✦ Best Seller
                        </span>
                    @endif
                    <span class="text-xs font-bold uppercase tracking-widest text-sage-600 bg-sage-100/80 px-3 py-1 rounded-full">
                        {{ $product->brand->name }} ({{ $product->brand->origin }})
                    </span>
                    <span class="text-xs font-medium text-stone-600 bg-white border border-sage-200 px-3 py-1 rounded-full">
                        Loại da: {{ $product->skin_type }}
                    </span>
                    <span class="text-xs font-medium text-stone-600 bg-white border border-sage-200 px-3 py-1 rounded-full">
                        Dung tích: {{ $product->volume }}
                    </span>
                </div>

                <h1 class="font-serif text-xl sm:text-3xl font-bold text-stone-900 leading-snug">
                    {{ $product->name }}
                </h1>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs sm:text-sm">
                    <span class="text-amber-400">★★★★★</span>
                    <span class="font-semibold text-stone-700">4.9/5</span>
                    <span class="text-stone-400">|</span>
                    <span class="text-emerald-600 font-semibold">Tồn kho tiêu chuẩn: {{ $product->stock }} sản phẩm</span>
                </div>
            </div>

            <!-- Price Box -->
            <div class="bg-sage-50/70 p-4 sm:p-5 rounded-2xl border border-sage-100 flex flex-wrap items-baseline gap-3 sm:gap-4">
                <span class="text-2xl sm:text-3xl font-bold text-sage-600">
                    {{ number_format($finalPrice, 0, ',', '.') }}₫
                </span>
                @if($product->sale_price && $product->sale_price < $product->price)
                    <span class="text-sm sm:text-base text-stone-400 line-through">
                        {{ number_format($product->price, 0, ',', '.') }}₫
                    </span>
                @endif
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500">Công dụng nổi bật</h3>
                <div class="text-sm text-stone-600 leading-relaxed space-y-2 [&_h2]:text-lg [&_h2]:font-bold [&_h2]:text-stone-800 [&_h3]:text-base [&_h3]:font-bold [&_h3]:text-sage-700 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_img]:rounded-2xl [&_img]:my-3 [&_img]:max-w-full [&_img]:h-auto [&_img]:border [&_img]:border-sage-100">
                    {!! $product->description !!}
                </div>
            </div>

            <!-- Active Ingredients Highlight -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-sage-100 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-sage-600">
                    🧪 Bảng Thành Phần Chi Tiết (INCI Ingredients)
                </h3>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(array_slice($ingredientsList, 0, 6) as $ing)
                        <span class="text-xs bg-sage-50 text-sage-700 px-3 py-1 rounded-full font-medium border border-sage-200/60">
                            {{ $ing }}
                        </span>
                    @endforeach
                </div>
                <p class="text-xs text-stone-500 leading-relaxed pt-1 border-t border-sage-50">
                    <strong>Toàn bộ thành phần:</strong> {{ $product->ingredients }}
                </p>
            </div>

            <!-- Add to Cart / Buy Now Form -->
            @php
                $isProductFavorited = auth()->check() && in_array((int) $product->id, $wishlistProductIds ?? [], true);
            @endphp
            <form action="{{ route('cart.add') }}" method="POST" class="flex flex-wrap items-center gap-2.5 sm:gap-3.5 pt-2">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="flex items-center border border-sage-200 rounded-full bg-white px-3 py-2">
                    <label for="quantity" class="text-xs text-stone-500 mr-2 pl-2">Số lượng:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="20" class="w-12 sm:w-14 text-center font-bold text-sm text-stone-800 focus:outline-none">
                </div>

                <button type="button"
                    data-wishlist-btn="{{ $product->id }}"
                    data-favorited="{{ $isProductFavorited ? '1' : '0' }}"
                    onclick="toggleWishlistProduct({{ $product->id }}, this)"
                    title="{{ $isProductFavorited ? 'Bỏ yêu thích sản phẩm này' : 'Thêm vào danh mục Yêu Thích' }}"
                    class="w-11 h-11 sm:w-12 sm:h-12 rounded-full flex items-center justify-center border transition-all duration-200 cursor-pointer flex-shrink-0 sm:order-last {{ $isProductFavorited ? 'opacity-100 bg-red-50 text-red-500 border-red-300 shadow-md shadow-red-200/80 ring-2 ring-red-400/50 scale-105' : 'opacity-40 hover:opacity-95 bg-white text-stone-400 hover:text-red-500 border-stone-200' }}">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 transition-transform duration-200" viewBox="0 0 24 24" fill="{{ $isProductFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>

                <button type="submit" class="w-full sm:w-auto sm:flex-1 sm:min-w-[145px] inline-flex items-center justify-center whitespace-nowrap bg-white hover:bg-sage-50 text-sage-600 border-2 border-sage-500 font-semibold py-3 sm:py-3.5 px-5 rounded-full transition text-sm cursor-pointer">
                    + Thêm Vào Giỏ
                </button>

                <button type="submit" name="buy_now" value="1" class="w-full sm:w-auto sm:flex-1 sm:min-w-[145px] inline-flex items-center justify-center whitespace-nowrap bg-sage-500 hover:bg-sage-600 text-white border-2 border-sage-500 hover:border-sage-600 font-semibold py-3 sm:py-3.5 px-5 rounded-full shadow-lg shadow-sage-200 transition text-sm cursor-pointer">
                    Mua Ngay →
                </button>
            </form>
        </div>
    </div>

    <!-- Product Reviews & Comments -->
    <section id="product-reviews" class="pt-8 sm:pt-10 border-t border-sage-100 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-sage-600">Đánh giá & Bình luận</p>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-stone-800 mt-1">
                    Nhận Xét Từ Khách Hàng
                    <span class="text-sm font-sans font-normal text-stone-500">({{ $totalReviews }} đánh giá)</span>
                </h2>
            </div>
        </div>

        {{-- Rating Summary + Form Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Rating Overview Card --}}
            <div class="lg:col-span-5 bg-white rounded-3xl border border-sage-100 p-6 shadow-xs space-y-5">
                <div class="flex items-center gap-5">
                    <div class="text-center flex-shrink-0">
                        <div class="text-4xl font-extrabold text-stone-900">
                            {{ $totalReviews > 0 ? number_format($averageRating, 1) : '0.0' }}
                        </div>
                        <div class="flex items-center justify-center gap-0.5 mt-1.5 text-amber-400">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-stone-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <p class="text-xs text-stone-500 mt-1">{{ $totalReviews }} lượt đánh giá</p>
                    </div>

                    <div class="flex-1 space-y-2">
                        @for($star = 5; $star >= 1; $star--)
                            @php $row = $ratingBreakdown[$star] ?? ['count' => 0, 'percent' => 0]; @endphp
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-8 font-semibold text-stone-600 text-right">{{ $star }} ★</span>
                                <div class="flex-1 h-2 rounded-full bg-stone-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-amber-400 transition-all" style="width: {{ $row['percent'] }}%"></div>
                                </div>
                                <span class="w-7 text-stone-400 text-right">{{ $row['count'] }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Comment & Rating Form --}}
            <div class="lg:col-span-7 bg-white rounded-3xl border border-sage-100 p-6 shadow-xs">
                @auth
                    <form action="{{ route('products.reviews.store', $product->slug) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-stone-500">Bình luận với tên:</span>
                                <strong class="text-stone-800">{{ auth()->user()->name }}</strong>
                                @if($hasCurrentUserPurchased)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                        ✓ Đã Mua
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- 1-5 Star Selector --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-2">
                                Đánh giá của bạn (1 – 5 sao) *
                            </label>
                            @php $selectedRating = (int) old('rating', 5); @endphp
                            <input type="hidden" name="rating" id="review-rating-input" value="{{ $selectedRating }}">
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-1" id="review-star-picker">
                                    @for($star = 1; $star <= 5; $star++)
                                        <button type="button"
                                                data-star="{{ $star }}"
                                                aria-label="Đánh giá {{ $star }} sao"
                                                class="review-star-btn p-1 rounded-lg transition transform hover:scale-110 cursor-pointer focus:outline-none">
                                            <svg class="w-7 h-7 {{ $star <= $selectedRating ? 'text-amber-400' : 'text-stone-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </button>
                                    @endfor
                                </div>
                                <span id="review-rating-label" class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                    5 sao • Tuyệt vời
                                </span>
                            </div>
                            @error('rating')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Comment Input --}}
                        <div>
                            <label for="review-comment-input" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1.5">
                                Nội dung bình luận *
                            </label>
                            <textarea id="review-comment-input"
                                      name="comment"
                                      rows="3"
                                      required
                                      maxlength="1000"
                                      placeholder="Chia sẻ cảm nhận của bạn về mùi hương, kết cấu, hiệu quả trên da..."
                                      class="w-full rounded-2xl border border-stone-200 bg-stone-50 focus:bg-white px-4 py-3 text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-sage-400 transition">{{ old('comment') }}</textarea>
                            @error('comment')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 bg-sage-600 hover:bg-sage-700 text-white font-semibold text-sm px-6 py-2.5 rounded-full shadow-sm transition cursor-pointer">
                                Gửi Đánh Giá
                            </button>
                        </div>
                    </form>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-center py-6 px-4 space-y-3 bg-sage-50/50 rounded-2xl border border-dashed border-sage-200">
                        <div class="w-11 h-11 rounded-2xl bg-white border border-sage-200 flex items-center justify-center text-sage-600 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div class="space-y-1 max-w-md">
                            <h3 class="text-base font-bold text-stone-800">Đăng nhập để bình luận & đánh giá</h3>
                            <p class="text-xs sm:text-sm text-stone-500">
                                Vui lòng đăng nhập tài khoản SHINE Beauty để gửi nhận xét và đánh giá sản phẩm từ 1 đến 5 sao.
                            </p>
                        </div>
                        <button type="button"
                                onclick="if (typeof openAuthModal === 'function') { openAuthModal('login'); } else { window.location.href = '{{ route('customer.login') }}'; }"
                                class="inline-flex items-center gap-2 bg-sage-600 hover:bg-sage-700 text-white font-semibold text-sm px-6 py-2.5 rounded-full shadow-sm transition cursor-pointer">
                            Đăng Nhập Để Bình Luận
                        </button>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Reviews List --}}
        <div class="space-y-4">
            @forelse($reviews as $review)
                @php
                    $reviewerName = $review->user->name ?? 'Khách hàng';
                    $reviewerInitial = mb_strtoupper(mb_substr($reviewerName, 0, 1));
                @endphp
                <article class="bg-white rounded-3xl border border-sage-100 p-5 sm:p-6 shadow-xs space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-sage-100 text-sage-700 font-bold text-sm flex items-center justify-center flex-shrink-0">
                                {{ $reviewerInitial }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-sm text-stone-900">{{ $reviewerName }}</span>
                                    @if($review->has_purchased)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                            ✓ Đã Mua
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="flex items-center gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-stone-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-xs text-stone-400">• {{ $review->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        @if(auth()->check() && (auth()->id() === $review->user_id || auth()->user()->is_admin))
                            <form action="{{ route('products.reviews.destroy', [$product->slug, $review->id]) }}" method="POST"
                                  data-confirm="true"
                                  data-confirm-type="danger"
                                  data-confirm-title="Xác nhận xóa bình luận"
                                  data-confirm-message="Bạn có chắc chắn muốn xóa bình luận này không?"
                                  data-confirm-detail="Bình luận sau khi xóa sẽ không thể khôi phục."
                                  data-confirm-btn="Xóa bình luận">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-500 hover:bg-red-50 border border-red-200 px-3 py-1 rounded-full transition cursor-pointer">
                                    Xóa
                                </button>
                            </form>
                        @endif
                    </div>

                    <p class="text-sm text-stone-700 leading-relaxed whitespace-pre-line pl-0 sm:pl-[52px]">{{ $review->comment }}</p>
                </article>
            @empty
                <div class="bg-white rounded-3xl border border-sage-100 p-8 text-center text-sm text-stone-400">
                    Chưa có bình luận nào cho sản phẩm này. Hãy là người đầu tiên chia sẻ cảm nhận!
                </div>
            @endforelse
        </div>
    </section>

    <script>
        (function () {
            const ratingInput = document.getElementById('review-rating-input');
            const picker = document.getElementById('review-star-picker');
            const labelEl = document.getElementById('review-rating-label');
            if (!ratingInput || !picker) return;

            const LABELS = {
                1: '1 sao • Rất tệ',
                2: '2 sao • Chưa hài lòng',
                3: '3 sao • Bình thường',
                4: '4 sao • Hài lòng',
                5: '5 sao • Tuyệt vời'
            };

            const buttons = Array.from(picker.querySelectorAll('.review-star-btn'));

            function renderStars(activeStar) {
                buttons.forEach((btn) => {
                    const star = Number(btn.dataset.star);
                    const svg = btn.querySelector('svg');
                    if (!svg) return;
                    svg.classList.toggle('text-amber-400', star <= activeStar);
                    svg.classList.toggle('text-stone-200', star > activeStar);
                });
                if (labelEl) {
                    labelEl.textContent = LABELS[activeStar] || LABELS[5];
                }
            }

            buttons.forEach((btn) => {
                const star = Number(btn.dataset.star);
                btn.addEventListener('mouseenter', () => renderStars(star));
                btn.addEventListener('click', () => {
                    ratingInput.value = String(star);
                    renderStars(star);
                });
            });

            picker.addEventListener('mouseleave', () => {
                renderStars(Number(ratingInput.value || 5));
            });

            renderStars(Number(ratingInput.value || 5));
        })();
    </script>

    <!-- Related Products -->
    @if($relatedProducts->isNotEmpty())
        <section class="pt-8 sm:pt-10 border-t border-sage-100 space-y-6">
            <h2 class="font-serif text-xl sm:text-2xl font-bold text-stone-800">Sản Phẩm Cùng Danh Mục</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($relatedProducts as $rel)
                    @include('partials.product-card', ['product' => $rel])
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
