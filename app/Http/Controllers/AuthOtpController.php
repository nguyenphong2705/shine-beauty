<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RegistrationOtpService;
use App\Services\SmsGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class AuthOtpController
 *
 * REST API Controller xử lý quy trình Gửi OTP, Xác thực OTP và Đăng ký tài khoản:
 * - POST /api/auth/otp/send     : Yêu cầu gửi mã OTP (Rate Limit: 3 lần/15 phút, Cooldown: 60 giây, Bcrypt Hash trong Redis)
 * - POST /api/auth/otp/verify   : Xác thực mã OTP (TTL: 3 phút, khóa ngay sau 5 lần sai hoặc ngay sau khi đúng)
 * - POST /api/auth/register     : Hoàn tất đăng ký tài khoản sau khi đã xác thực OTP
 */
class AuthOtpController extends Controller
{
    public function __construct(
        private readonly RegistrationOtpService $otpService
    ) {}

    /**
     * POST /api/auth/otp/send
     * Gửi mã OTP 6 chữ số đến số điện thoại đăng ký tài khoản.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone'    => ['required', 'string', 'max:30'],
            'provider' => ['nullable', 'string', 'in:esms,zalo_zns,twilio,log'],
        ]);

        $normalizedPhone = SmsGatewayService::normalizeVietnamPhone($validated['phone']);

        // Kiểm tra nếu số điện thoại đã được đăng ký bởi tài khoản khác
        if (User::query()->where('phone', $normalizedPhone)->orWhere('phone', trim($validated['phone']))->exists()) {
            return response()->json([
                'ok' => false,
                'code' => 'PHONE_ALREADY_REGISTERED',
                'message' => 'Số điện thoại này đã được đăng ký tài khoản trên hệ thống.',
            ], 409);
        }

        $result = $this->otpService->sendOtp($normalizedPhone, $validated['provider'] ?? null);
        $httpStatus = (int) ($result['status'] ?? 200);
        unset($result['status']);

        $headers = [];
        if ($httpStatus === 429 && isset($result['retry_after'])) {
            $headers['Retry-After'] = (string) $result['retry_after'];
        }

        return response()->json($result, $httpStatus, $headers);
    }

    /**
     * POST /api/auth/otp/verify
     * Xác thực mã OTP 6 chữ số.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'otp'   => ['required', 'string', 'max:10'],
        ]);

        $result = $this->otpService->verifyOtp($validated['phone'], $validated['otp']);
        $httpStatus = (int) ($result['status'] ?? 200);
        unset($result['status']);

        return response()->json($result, $httpStatus);
    }

    /**
     * POST /api/auth/register
     * Đăng ký tài khoản mới sau khi đã xác thực OTP (bằng verification_token hoặc otp trực tiếp).
     */
    public function registerWithOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'username'           => ['required', 'string', 'max:100', 'unique:users,username'],
            'email'              => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'              => ['required', 'string', 'max:30'],
            'address'            => ['nullable', 'string', 'max:500'],
            'password'           => ['required', 'string', 'min:6'],
            'verification_token' => ['nullable', 'string'],
            'otp'                => ['nullable', 'string', 'size:6'],
        ]);

        $normalizedPhone = SmsGatewayService::normalizeVietnamPhone($validated['phone']);

        if (User::query()->where('phone', $normalizedPhone)->exists()) {
            return response()->json([
                'ok' => false,
                'code' => 'PHONE_ALREADY_REGISTERED',
                'message' => 'Số điện thoại này đã được đăng ký tài khoản.',
            ], 409);
        }

        $isPhoneVerified = false;
        if (! empty($validated['verification_token'])) {
            $isPhoneVerified = $this->otpService->consumeVerifiedPhoneToken($normalizedPhone, $validated['verification_token']);
        } elseif (! empty($validated['otp'])) {
            $verifyRes = $this->otpService->verifyOtp($normalizedPhone, $validated['otp']);
            if (! empty($verifyRes['ok'])) {
                $isPhoneVerified = true;
                if (! empty($verifyRes['verification_token'])) {
                    $this->otpService->consumeVerifiedPhoneToken($normalizedPhone, $verifyRes['verification_token']);
                }
            } else {
                $status = (int) ($verifyRes['status'] ?? 422);
                unset($verifyRes['status']);
                return response()->json($verifyRes, $status);
            }
        }

        if (! $isPhoneVerified) {
            return response()->json([
                'ok' => false,
                'code' => 'OTP_VERIFICATION_REQUIRED',
                'message' => 'Vui lòng xác thực mã OTP số điện thoại trước khi hoàn tất đăng ký tài khoản.',
            ], 422);
        }

        $user = User::create([
            'name'     => trim($validated['name']),
            'username' => trim($validated['username']),
            'email'    => trim($validated['email']),
            'phone'    => $normalizedPhone,
            'address'  => $validated['address'] ?? 'TP. Hồ Chí Minh',
            'password' => Hash::make($validated['password']),
            'is_admin' => false,
            'role'     => 'customer',
        ]);

        if ($request->hasSession()) {
            Auth::login($user, true);
            $request->session()->regenerate();
            CartController::syncUserCartToSession($user);
        }

        return response()->json([
            'ok' => true,
            'code' => 'REGISTER_SUCCESS',
            'message' => 'Đăng ký tài khoản và xác thực OTP thành công!',
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'phone'    => $user->phone,
            ],
        ], 201);
    }
}
