<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Class ProductController
 *
 * Điều hướng và xử lý logic hiển thị sản phẩm dành cho Khách hàng:
 * - Danh sách sản phẩm kèm bộ lọc đa tiêu chí (danh mục, thương hiệu, loại da, khoảng giá, sắp xếp, tìm kiếm).
 * - Hỗ trợ cả 3 kiểu phản hồi: HTML trang đầy đủ, HTML partial (AJAX không reload), và REST API JSON.
 * - Trang chi tiết sản phẩm và gợi ý các sản phẩm liên quan cùng danh mục.
 */
class ProductController extends Controller
{
    /** Số sản phẩm mỗi trang */
    private const PER_PAGE = 12;

    /** Các giá trị hợp lệ cho tham số sort */
    private const SORT_OPTIONS = ['newest', 'price_asc', 'price_desc', 'name_asc'];

    /** Các giá trị hợp lệ cho skin_type */
    private const SKIN_TYPES = ['Da dầu', 'Da khô', 'Da nhạy cảm', 'Mọi loại da'];

    /**
     * Danh sách sản phẩm + bộ lọc qua Query Parameters.
     *
     * Hỗ trợ: category (slug), brand (slug/tên/id), skin_type, min_price, max_price,
     * price_range (tương thích cũ), sort, search, page.
     *
     * Cùng một endpoint `/san-pham` phục vụ 3 kiểu phản hồi (không tạo route trùng lặp):
     *  - Trình duyệt thường       → trang HTML đầy đủ (F5 / chia sẻ URL vẫn đúng bộ lọc)
     *  - Header `X-Partial: products` → chỉ HTML khối kết quả (cập nhật không reload trang)
     *  - `Accept: application/json` → REST API JSON có phân trang
     */
    public function index(Request $request)
    {
        $categories = \Illuminate\Support\Facades\Cache::remember('catalog.categories.all.v1', 300, fn () => Category::all());
        $brands = \Illuminate\Support\Facades\Cache::remember('catalog.brands.all.v1', 300, fn () => Brand::all());

        $filters = $this->sanitizeFilters($request, $categories, $brands);

        $query = Product::visible()->with(['category', 'brand']);

        // Search filter (tìm theo Mã SP SKU, Tên SP, Hoạt chất, Mô tả)
        if ($filters['search'] !== '') {
            $search = $filters['search'];
            // Cột sku có trong migration nhưng có thể chưa được migrate trên DB hiện tại
            $hasSku = \Illuminate\Support\Facades\Cache::remember('schema.products.has_sku', 86400, fn () => Schema::hasColumn('products', 'sku'));
            $query->where(function ($q) use ($search, $hasSku) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('ingredients', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
                if ($hasSku) {
                    $q->orWhere('sku', 'ilike', "%{$search}%");
                }
            });
        }

        // Category filter (theo slug). Slug không tồn tại → trả về rỗng + thông báo.
        $activeCategory = null;
        $categoryNotFound = false;
        if ($filters['category'] !== null) {
            $activeCategory = $categories->firstWhere('slug', $filters['category']);
            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
            } else {
                $categoryNotFound = true;
                $query->whereRaw('1 = 0');
            }
        }

        // Brand filter (theo slug của tên thương hiệu)
        $activeBrand = null;
        if ($filters['brand'] !== null) {
            $activeBrand = $brands->first(fn (Brand $b) => Str::slug($b->name) === $filters['brand']);
            $activeBrand
                ? $query->where('brand_id', $activeBrand->id)
                : $query->whereRaw('1 = 0');
        }

        // Skin Type filter
        if ($filters['skin_type'] !== null) {
            $query->where('skin_type', $filters['skin_type']);
        }

        // Price filter (giá thực tế = giá sale nếu có)
        if ($filters['min_price'] !== null) {
            $query->whereRaw('COALESCE(sale_price, price) >= ?', [$filters['min_price']]);
        }
        if ($filters['max_price'] !== null) {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [$filters['max_price']]);
        }

        // Sort
        match ($filters['sort']) {
            'price_asc' => $query->orderByRaw('COALESCE(sale_price, price) ASC')->orderBy('id'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) DESC')->orderBy('id'),
            'name_asc' => $query->orderBy('name')->orderBy('id'),
            default => $query->latest()->orderByDesc('id'),
        };

        $products = $query->paginate(self::PER_PAGE)->withQueryString();

        // Trang vượt quá số trang thực tế → về trang cuối có dữ liệu
        if ($products->isEmpty() && $products->currentPage() > 1 && $products->lastPage() >= 1 && $products->total() > 0) {
            $products = $query->paginate(self::PER_PAGE, ['*'], 'page', $products->lastPage())->withQueryString();
        }

        // REST API JSON
        if ($request->wantsJson()) {
            return response()->json([
                'filters' => $filters,
                'category_not_found' => $categoryNotFound,
                'data' => $products->getCollection()->map(fn (Product $p) => [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => (int) $p->price,
                    'sale_price' => $p->sale_price !== null ? (int) $p->sale_price : null,
                    'stock' => (int) $p->stock,
                    'skin_type' => $p->skin_type,
                    'image' => $p->images[0] ?? null,
                    'category' => $p->category ? ['name' => $p->category->name, 'slug' => $p->category->slug] : null,
                    'brand' => $p->brand ? ['name' => $p->brand->name, 'slug' => Str::slug($p->brand->name)] : null,
                    'url' => route('products.show', $p->slug),
                ])->values(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        }

        $viewData = compact('products', 'categories', 'brands', 'filters', 'activeCategory', 'activeBrand', 'categoryNotFound');

        // Chỉ trả về khối kết quả để JS thay thế mà không reload trang
        if ($request->header('X-Partial') === 'products') {
            return response()
                ->view('products._results', $viewData)
                ->header('Vary', 'X-Partial');
        }

        return response()
            ->view('products.index', $viewData)
            ->header('Vary', 'X-Partial');
    }

    /**
     * Validate & chuẩn hóa query params. Giá trị không hợp lệ bị bỏ qua (không làm hỏng trang),
     * giúp URL chia sẻ sai/thủ công vẫn hiển thị được.
     */
    private function sanitizeFilters(Request $request, $categories, $brands): array
    {
        $str = function (string $key, int $max = 100): ?string {
            $v = request()->query($key);
            if (!is_string($v)) {
                return null;
            }
            $v = trim($v);
            return ($v === '' || mb_strlen($v) > $max) ? null : $v;
        };

        // category: slug (a-z0-9-). Hỗ trợ ID số cũ → quy đổi sang slug.
        $category = $str('category');
        if ($category === 'ALL') {
            $category = null;
        } elseif ($category !== null && ctype_digit($category)) {
            $category = optional($categories->firstWhere('id', (int) $category))->slug;
        } elseif ($category !== null && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $category)) {
            $category = Str::slug($category) ?: null;
        }

        // brand: slug. Hỗ trợ tên / ID cũ → quy đổi sang slug.
        $brand = $str('brand');
        if ($brand === 'ALL') {
            $brand = null;
        } elseif ($brand !== null && ctype_digit($brand)) {
            $found = $brands->firstWhere('id', (int) $brand);
            $brand = $found ? Str::slug($found->name) : null;
        } elseif ($brand !== null) {
            $brand = Str::slug($brand) ?: null;
        }

        $skinType = $str('skin_type');
        if (!in_array($skinType, self::SKIN_TYPES, true)) {
            $skinType = null;
        }

        $toPrice = function (?string $v): ?int {
            if ($v === null || !ctype_digit($v)) {
                return null;
            }
            return min((int) $v, 1_000_000_000);
        };
        $minPrice = $toPrice($str('min_price', 12));
        $maxPrice = $toPrice($str('max_price', 12));

        // Tương thích tham số price_range cũ (link từ trang chủ / bookmark cũ)
        if ($minPrice === null && $maxPrice === null) {
            switch ($str('price_range')) {
                case 'UNDER_500': $maxPrice = 499999; break;
                case '500_TO_1000': $minPrice = 500000; $maxPrice = 1000000; break;
                case 'ABOVE_1000': $minPrice = 1000001; break;
            }
        }
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $sort = $str('sort');
        if (!in_array($sort, self::SORT_OPTIONS, true)) {
            $sort = 'newest';
        }

        $search = mb_substr((string) ($str('search', 255) ?? ''), 0, 100);

        return [
            'category' => $category,
            'brand' => $brand,
            'skin_type' => $skinType,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort' => $sort,
            'search' => $search,
        ];
    }

    /**
     * Hiển thị trang chi tiết sản phẩm và các sản phẩm liên quan.
     *
     * Chức năng:
     * - Tìm kiếm sản phẩm theo đường dẫn thân thiện (slug) kèm thông tin danh mục và thương hiệu.
     * - Tự động ném lỗi 404 (firstOrFail) nếu không tìm thấy slug tương ứng.
     * - Lấy tối đa 4 sản phẩm cùng danh mục để hiển thị ở mục "Sản phẩm liên quan".
     *
     * @param string $slug Chuỗi định danh URL thân thiện của sản phẩm
     * @return View Giao diện chi tiết sản phẩm (products.show)
     */
    public function show(string $slug)
    {
        $product = Product::visible()
            ->with(['category', 'brand'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = Product::visible()
            ->with(['category', 'brand'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        $reviews = $product->reviews()
            ->with('user:id,name,phone')
            ->latest()
            ->get();

        // Kiểm tra các đơn hàng hợp lệ (không bị hủy) đã mua sản phẩm này để gắn nhãn "Đã Mua"
        $productOrders = Order::query()
            ->where('status', '!=', 'CANCELLED')
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->get(['user_id', 'phone']);

        $purchasedUserIds = $productOrders->pluck('user_id')->filter()->map(fn ($id) => (int) $id)->flip();
        $purchasedPhones = $productOrders->pluck('phone')->filter()->map(fn ($p) => trim((string) $p))->flip();

        $reviews->each(function (ProductReview $review) use ($purchasedUserIds, $purchasedPhones): void {
            $u = $review->user;
            $matched = $review->is_verified_purchase
                || ($u && ($purchasedUserIds->has((int) $u->id) || (!empty($u->phone) && $purchasedPhones->has(trim((string) $u->phone)))));
            $review->setAttribute('has_purchased', $matched);
        });

        $totalReviews = $reviews->count();
        $averageRating = $totalReviews > 0 ? round((float) $reviews->avg('rating'), 1) : 0.0;
        $ratingBreakdown = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = $reviews->where('rating', $star)->count();
            $ratingBreakdown[$star] = [
                'count' => $count,
                'percent' => $totalReviews > 0 ? (int) round(($count / $totalReviews) * 100) : 0,
            ];
        }

        $hasCurrentUserPurchased = false;
        if (Auth::check()) {
            $hasCurrentUserPurchased = $this->hasUserPurchasedProduct(Auth::user(), $product);
        }

        return view('products.show', compact(
            'product',
            'relatedProducts',
            'reviews',
            'totalReviews',
            'averageRating',
            'ratingBreakdown',
            'hasCurrentUserPurchased'
        ));
    }

    /**
     * Lưu bình luận và đánh giá (1–5 sao) cho sản phẩm. Yêu cầu khách hàng đã đăng nhập.
     */
    public function storeReview(Request $request, string $slug): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()
                ->route('products.show', $slug)
                ->with('error', 'Vui lòng đăng nhập để bình luận và đánh giá sản phẩm.');
        }

        $product = Product::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá từ 1 đến 5 sao.',
            'rating.min' => 'Đánh giá tối thiểu là 1 sao.',
            'rating.max' => 'Đánh giá tối đa là 5 sao.',
            'comment.required' => 'Vui lòng nhập nội dung bình luận.',
            'comment.min' => 'Nội dung bình luận tối thiểu 3 ký tự.',
            'comment.max' => 'Nội dung bình luận tối đa 1000 ký tự.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $hasPurchased = $this->hasUserPurchasedProduct($user, $product);

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => (int) $validated['rating'],
            'comment' => trim($validated['comment']),
            'is_verified_purchase' => $hasPurchased,
        ]);

        return redirect(route('products.show', $product->slug) . '#product-reviews')
            ->with('success', 'Cảm ơn bạn đã gửi bình luận & đánh giá sản phẩm!');
    }

    /**
     * Xóa bình luận của chính người dùng (hoặc quản trị viên).
     */
    public function destroyReview(string $slug, ProductReview $review): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('products.show', $slug);
        }

        $product = Product::where('slug', $slug)->firstOrFail();
        if ((int) $review->product_id !== (int) $product->id) {
            abort(404);
        }

        $user = Auth::user();
        if ((int) $review->user_id !== (int) $user->id && !$user->is_admin) {
            return redirect(route('products.show', $product->slug) . '#product-reviews')
                ->with('error', 'Bạn không có quyền xóa bình luận này.');
        }

        $review->delete();

        return redirect(route('products.show', $product->slug) . '#product-reviews')
            ->with('success', 'Đã xóa bình luận.');
    }

    private function hasUserPurchasedProduct(User $user, Product $product): bool
    {
        return Order::query()
            ->where('status', '!=', 'CANCELLED')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if (!empty($user->phone)) {
                    $q->orWhere('phone', $user->phone);
                }
            })
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();
    }
}
