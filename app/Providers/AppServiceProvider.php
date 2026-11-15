<?php

namespace App\Providers;

use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $compiledPath = env('VIEW_COMPILED_PATH', '/tmp/laravel-views');
        if ($compiledPath && ! is_dir($compiledPath)) {
            @mkdir($compiledPath, 0777, true);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $localFlagFile = sys_get_temp_dir().'/shine_boot_schema_checked_v1.flag';
            if (! is_file($localFlagFile)) {
                if (! Cache::get('app.boot_schema_checked_v1')) {
                    \App\Models\User::ensureCustomerFeaturesSchema();
                    \App\Models\Product::ensureUnder500kProductsSeeded();
                    Cache::put('app.boot_schema_checked_v1', true, now()->addHours(12));
                }
                @file_put_contents($localFlagFile, '1');
            }
        } catch (\Throwable) {
            // Bỏ qua nếu bảng chưa được migrate
        }

        $flushCatalogCache = static function (): void {
            foreach ([
                'home.categories.v1',
                'home.brands.v1',
                'home.total_products_count',
                'home.best_sellers.v1',
                'catalog.categories.all.v1',
                'catalog.brands.all.v1',
                'blog.recommended_products.v1',
            ] as $key) {
                Cache::forget($key);
            }
        };

        \App\Models\Product::saved($flushCatalogCache);
        \App\Models\Product::deleted($flushCatalogCache);
        \App\Models\Category::saved($flushCatalogCache);
        \App\Models\Category::deleted($flushCatalogCache);
        \App\Models\Brand::saved($flushCatalogCache);
        \App\Models\Brand::deleted($flushCatalogCache);

        View::composer([
            'layouts.app',
            'home',
            'products.index',
            'products._results',
            'products.show',
            'blog.index',
            'blog.show',
            'customer.wishlist',
        ], function ($view): void {
            static $cachedWishlistIds = null;

            if ($cachedWishlistIds === null) {
                $cachedWishlistIds = [];
                try {
                    if (Auth::check()) {
                        $cachedWishlistIds = Wishlist::query()
                            ->where('user_id', Auth::id())
                            ->pluck('product_id')
                            ->map(fn ($id) => (int) $id)
                            ->all();
                    }
                } catch (\Throwable) {
                    $cachedWishlistIds = [];
                }
                View::share('wishlistProductIds', $cachedWishlistIds);
            }

            $view->with('wishlistProductIds', $cachedWishlistIds);
        });
    }
}
