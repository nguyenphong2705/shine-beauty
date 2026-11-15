<?php

use App\Http\Controllers\AuthOtpController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Hệ Thống Thanh Toán Tự Động & Xác Thực OTP Đăng Ký Tài Khoản
|--------------------------------------------------------------------------
*/

// Nhóm API Gửi & Xác thực OTP Đăng ký tài khoản (Bcrypt Hash trong Redis + Rate Limit 3 lần/15p + Cooldown 60s + TTL 3p)
Route::prefix('auth')
    ->middleware(['web'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->group(function (): void {
        Route::post('/otp/send', [AuthOtpController::class, 'sendOtp'])->name('api.auth.otp.send');
        Route::post('/otp/verify', [AuthOtpController::class, 'verifyOtp'])->name('api.auth.otp.verify');
        Route::post('/register', [AuthOtpController::class, 'registerWithOtp'])->name('api.auth.register');
    });

// Nhóm API đơn hàng & thanh toán (hỗ trợ cả Session Auth của trình duyệt và API Stateless)
Route::middleware(['web'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->group(function (): void {
        Route::post('/orders', [OrderController::class, 'store'])->name('api.orders.store');
        Route::get('/orders/{orderCode}', [OrderController::class, 'show'])->name('api.orders.show');
        Route::get('/orders/{orderCode}/payment', [PaymentController::class, 'show'])->name('api.orders.payment');
        Route::get('/orders/{orderCode}/payment-status', [PaymentController::class, 'status'])->name('api.orders.payment.status');
    });

// Webhook SePay nhận thông báo biến động số dư tự động (Stateless, xác thực bằng Header Authorization: Apikey)
Route::post('/webhooks/sepay', [PaymentWebhookController::class, 'handleSePay'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('api.webhooks.sepay');
