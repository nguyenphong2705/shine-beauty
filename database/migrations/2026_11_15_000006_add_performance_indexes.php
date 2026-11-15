<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_category_id ON products (category_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_brand_id ON products (brand_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_best_seller ON products (is_best_seller, id DESC)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_skin_type ON products (skin_type)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_price ON products (price)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_created_at ON products (created_at DESC)');
        }

        if (Schema::hasTable('orders')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders (user_id, created_at DESC)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_phone ON orders (phone)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_status ON orders (status)');
        }

        if (Schema::hasTable('coupons')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_coupons_active_code ON coupons (code, is_active)');
        }

        if (Schema::hasTable('wishlists')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_wishlists_user_product ON wishlists (user_id, product_id)');
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_products_category_id');
        DB::statement('DROP INDEX IF EXISTS idx_products_brand_id');
        DB::statement('DROP INDEX IF EXISTS idx_products_best_seller');
        DB::statement('DROP INDEX IF EXISTS idx_products_skin_type');
        DB::statement('DROP INDEX IF EXISTS idx_products_price');
        DB::statement('DROP INDEX IF EXISTS idx_products_created_at');
        DB::statement('DROP INDEX IF EXISTS idx_orders_user_id');
        DB::statement('DROP INDEX IF EXISTS idx_orders_phone');
        DB::statement('DROP INDEX IF EXISTS idx_orders_status');
        DB::statement('DROP INDEX IF EXISTS idx_coupons_active_code');
        DB::statement('DROP INDEX IF EXISTS idx_wishlists_user_product');
    }
};
