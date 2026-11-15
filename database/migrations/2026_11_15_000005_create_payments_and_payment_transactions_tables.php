<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bổ sung các cột cần thiết vào bảng orders hiện có (không phá vỡ dữ liệu cũ)
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_code')) {
                $table->string('order_code', 40)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('orders', 'order_status')) {
                $table->string('order_status', 30)->default('PENDING')->after('status');
            }
            if (!Schema::hasColumn('orders', 'payment_expires_at')) {
                $table->timestamp('payment_expires_at')->nullable()->after('paid_at');
            }
        });

        // Đồng bộ order_code và order_status cho các đơn hàng đã có sẵn
        $existingOrders = DB::table('orders')->select(['id', 'order_code', 'status', 'created_at'])->get();
        foreach ($existingOrders as $ord) {
            $code = $ord->order_code ?: ('DH' . date('ymd', strtotime((string) ($ord->created_at ?? 'now'))) . str_pad((string) $ord->id, 4, '0', STR_PAD_LEFT));
            DB::table('orders')
                ->where('id', $ord->id)
                ->update([
                    'order_code' => $code,
                    'order_status' => $ord->status ?: 'PENDING',
                ]);
        }

        // 2. Tạo bảng payments quản lý thông tin thanh toán của từng đơn hàng
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->unsignedBigInteger('amount');
                $table->unsignedBigInteger('received_amount')->default(0);
                $table->string('payment_method', 40)->default('BANK_TRANSFER');
                $table->string('transaction_code', 100)->nullable()->index();
                $table->string('transfer_content', 120)->unique();
                $table->string('bank_code', 40)->nullable();
                $table->string('bank_account', 60)->nullable();
                $table->string('status', 40)->default('PENDING_PAYMENT')->index();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('verification_note')->nullable();
                $table->jsonb('status_history')->nullable();
                $table->timestamps();

                $table->index(['order_id', 'status']);
            });
        }

        // 3. Tạo bảng payment_transactions lưu lịch sử giao dịch Webhook SePay & đối soát
        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
                $table->string('provider_transaction_id', 100)->unique();
                $table->string('reference_code', 100)->nullable()->index();
                $table->string('gateway', 60)->nullable();
                $table->string('account_number', 60)->nullable();
                $table->string('transfer_type', 20)->default('in');
                $table->unsignedBigInteger('amount');
                $table->text('transfer_content')->nullable();
                $table->timestamp('transaction_date')->nullable();
                $table->string('status', 40)->default('PROCESSED')->index();
                $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reconciled_at')->nullable();
                $table->text('reconciliation_note')->nullable();
                $table->jsonb('raw_payload');
                $table->timestamps();

                $table->index(['order_id', 'status']);
                $table->index('transaction_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payments');

        Schema::table('orders', function (Blueprint $table) {
            $cols = array_filter(
                ['order_code', 'order_status', 'payment_expires_at'],
                fn (string $c): bool => Schema::hasColumn('orders', $c)
            );
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
