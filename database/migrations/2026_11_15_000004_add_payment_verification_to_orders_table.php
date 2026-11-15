<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status', 30)->default('UNPAID')->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'transaction_code')) {
                $table->string('transaction_code', 64)->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('transaction_code');
            }
        });

        // Đồng bộ trạng thái thanh toán cho các đơn hàng hiện có
        DB::table('orders')
            ->whereIn('status', ['PENDING', 'CANCELLED'])
            ->update([
                'payment_status' => 'UNPAID',
            ]);

        DB::table('orders')
            ->where('status', 'COMPLETED')
            ->update([
                'payment_status' => 'PAID',
                'paid_at' => DB::raw('COALESCE(updated_at, NOW())'),
            ]);

        DB::table('orders')
            ->whereIn('status', ['CONFIRMED', 'SHIPPING'])
            ->whereIn('payment_method', ['BANK_TRANSFER', 'MOMO_QR'])
            ->update([
                'payment_status' => 'PAID',
                'transaction_code' => 'FT26310001',
                'paid_at' => DB::raw('COALESCE(updated_at, NOW())'),
            ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = array_filter(['payment_status', 'transaction_code', 'paid_at'], fn ($c) => Schema::hasColumn('orders', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
