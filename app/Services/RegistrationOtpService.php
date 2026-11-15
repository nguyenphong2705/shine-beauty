<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Class RegistrationOtpService
 *
 * Quản lý quy trình sinh mã, mã hóa (Bcrypt Hash), giới hạn tần suất (Rate Limiting)
 * và xác thực OTP đăng ký tài khoản lưu trữ trên Redis:
 * - Mã OTP luôn được băm bằng Hash::make() trước khi lưu vào Redis (không lưu plain text).
 * - Rate Limiting: Tối đa 3 lần gửi / 15 phút (900s) cho mỗi SĐT, mỗi lần cách nhau ít nhất 60 giây.
 * - Hiệu lực (TTL): 3 phút (180 giây).
 * - Tự động vô hiệu hóa (xóa khỏi Redis) ngay sau khi xác thực thành công hoặc nhập sai đủ 5 lần.
 */
class RegistrationOtpService
{
    /** Thời gian hiệu lực của mã OTP: 3 phút (180 giây) */
    public const OTP_TTL_SECONDS = 180;

    /** Khoảng cách tối thiểu giữa 2 lần gửi OTP liên tiếp: 60 giây */
    public const COOLDOWN_SECONDS = 60;

    /** Khung thời gian giới hạn số lần gửi OTP: 15 phút (900 giây) */
    public const RATE_WINDOW_SECONDS = 900;

    /** Số lần gửi OTP tối đa trong vòng 15 phút cho mỗi số điện thoại */
    public const MAX_SENDS_PER_WINDOW = 3;

    /** Số lần nhập sai tối đa trước khi mã OTP tự động bị hủy */
    public const MAX_VERIFY_ATTEMPTS = 5;

    /** Thời hạn hiệu lực của phiên xác thực SĐT sau khi verify OTP thành công: 10 phút */
    public const VERIFIED_TOKEN_TTL_SECONDS = 600;

    public function __construct(
        private readonly SmsGatewayService $smsGateway
    ) {}

    public static function recordKey(string $normalizedPhone): string
    {
        return "otp:register:record:{$normalizedPhone}";
    }

    public static function cooldownKey(string $normalizedPhone): string
    {
        return "otp:register:cooldown:{$normalizedPhone}";
    }

    public static function windowKey(string $normalizedPhone): string
    {
        return "otp:register:window:{$normalizedPhone}";
    }

    public static function verifiedKey(string $normalizedPhone): string
    {
        return "otp:register:verified:{$normalizedPhone}";
    }

    /**
     * Yêu cầu tạo và gửi mã OTP đăng ký tài khoản đến số điện thoại.
     *
     * @param string $rawPhone Số điện thoại khách hàng
     * @param string|null $provider Cổng gửi ('esms', 'zalo_zns', 'twilio')
     * @return array<string, mixed>
     */
    public function sendOtp(string $rawPhone, ?string $provider = null): array
    {
        $phone = SmsGatewayService::normalizeVietnamPhone($rawPhone);
        if (! preg_match('/^0\d{8,10}$/', $phone)) {
            return [
                'ok' => false,
                'status' => 422,
                'code' => 'INVALID_PHONE_FORMAT',
                'message' => 'Số điện thoại không hợp lệ. Vui lòng nhập số điện thoại Việt Nam (10 chữ số, bắt đầu bằng số 0).',
            ];
        }

        $nowTs = now()->timestamp;

        // 1. Kiểm tra Cooldown 60 giây giữa 2 lần gửi liên tiếp
        $cooldownUntil = (int) Cache::get(self::cooldownKey($phone), 0);
        if ($cooldownUntil > $nowTs) {
            $retryAfter = $cooldownUntil - $nowTs;
            return [
                'ok' => false,
                'status' => 429,
                'code' => 'OTP_COOLDOWN_ACTIVE',
                'retry_after' => $retryAfter,
                'message' => "Vui lòng đợi {$retryAfter} giây nữa trước khi yêu cầu gửi lại mã OTP.",
            ];
        }

        // 2. Kiểm tra Rate Limiting: tối đa 3 lần gửi trong vòng 15 phút (900 giây)
        $windowHistory = Cache::get(self::windowKey($phone), []);
        if (! is_array($windowHistory)) {
            $windowHistory = [];
        }
        $windowStart = $nowTs - self::RATE_WINDOW_SECONDS;
        $recentSends = array_values(array_filter(
            $windowHistory,
            static fn ($ts): bool => is_int($ts) && $ts > $windowStart
        ));

        if (count($recentSends) >= self::MAX_SENDS_PER_WINDOW) {
            $oldestSendTs = $recentSends[0];
            $retryAfter = max(1, ($oldestSendTs + self::RATE_WINDOW_SECONDS) - $nowTs);
            return [
                'ok' => false,
                'status' => 429,
                'code' => 'OTP_RATE_LIMIT_EXCEEDED',
                'retry_after' => $retryAfter,
                'max_sends_per_15m' => self::MAX_SENDS_PER_WINDOW,
                'message' => "Số điện thoại {$phone} đã yêu cầu tối đa 3 lần gửi OTP trong 15 phút. Vui lòng thử lại sau {$retryAfter} giây.",
            ];
        }

        // 3. Sinh mã OTP 6 chữ số ngẫu nhiên an toàn mật mã (CSPRNG)
        $otpPlain = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 4. Băm (Hash) mã OTP bằng Bcrypt trước khi lưu vào Redis (tuyệt đối không lưu plain text)
        $otpRecord = [
            'phone'        => $phone,
            'otp_hash'     => Hash::make($otpPlain),
            'attempts'     => 0,
            'max_attempts' => self::MAX_VERIFY_ATTEMPTS,
            'created_at'   => $nowTs,
            'expires_at'   => $nowTs + self::OTP_TTL_SECONDS,
        ];

        Cache::put(self::recordKey($phone), $otpRecord, self::OTP_TTL_SECONDS);

        // 5. Cập nhật bộ đếm Rate Limit (15 phút) và Cooldown (60 giây)
        $recentSends[] = $nowTs;
        Cache::put(self::windowKey($phone), $recentSends, self::RATE_WINDOW_SECONDS);
        Cache::put(self::cooldownKey($phone), $nowTs + self::COOLDOWN_SECONDS, self::COOLDOWN_SECONDS);

        // 6. Gửi tin nhắn qua cổng SMS / Zalo ZNS / Twilio
        $gatewayResult = $this->smsGateway->sendRegistrationOtp($phone, $otpPlain, $provider);

        $response = [
            'ok' => true,
            'status' => 200,
            'code' => 'OTP_SENT',
            'phone' => $phone,
            'expires_in' => self::OTP_TTL_SECONDS,
            'cooldown_seconds' => self::COOLDOWN_SECONDS,
            'remaining_sends_in_window' => max(0, self::MAX_SENDS_PER_WINDOW - count($recentSends)),
            'gateway' => [
                'provider' => $gatewayResult['provider'] ?? 'esms',
                'message_id' => $gatewayResult['message_id'] ?? null,
                'simulated' => (bool) ($gatewayResult['simulated'] ?? false),
            ],
            'message' => "Mã xác thực OTP (hiệu lực 3 phút) đã được gửi tới số điện thoại {$phone}.",
        ];

        // Chỉ trả kèm dev_otp_preview khi chạy local/testing và gateway đang ở chế độ mô phỏng để tiện kiểm thử
        if (app()->environment(['local', 'testing']) && ! empty($gatewayResult['simulated'])) {
            $response['dev_otp_preview'] = $otpPlain;
        }

        return $response;
    }

    /**
     * Xác thực mã OTP do người dùng nhập vào.
     * - Tự động vô hiệu hóa ngay khi xác thực thành công.
     * - Tự động vô hiệu hóa ngay khi nhập sai đủ 5 lần.
     *
     * @return array<string, mixed>
     */
    public function verifyOtp(string $rawPhone, string $otpInput): array
    {
        $phone = SmsGatewayService::normalizeVietnamPhone($rawPhone);
        $otpCode = trim($otpInput);

        if (! preg_match('/^\d{6}$/', $otpCode)) {
            return [
                'ok' => false,
                'status' => 422,
                'code' => 'INVALID_OTP_FORMAT',
                'message' => 'Mã OTP phải gồm đúng 6 chữ số.',
            ];
        }

        $record = Cache::get(self::recordKey($phone));
        $nowTs = now()->timestamp;

        // Kiểm tra tồn tại và thời hạn 3 phút
        if (! is_array($record) || empty($record['otp_hash']) || $nowTs >= (int) ($record['expires_at'] ?? 0)) {
            Cache::forget(self::recordKey($phone));
            return [
                'ok' => false,
                'status' => 422,
                'code' => 'OTP_EXPIRED_OR_NOT_FOUND',
                'message' => 'Mã OTP không tồn tại hoặc đã hết hạn (quá 3 phút). Vui lòng yêu cầu gửi mã OTP mới.',
            ];
        }

        $attempts = (int) ($record['attempts'] ?? 0);
        if ($attempts >= self::MAX_VERIFY_ATTEMPTS) {
            Cache::forget(self::recordKey($phone));
            return [
                'ok' => false,
                'status' => 429,
                'code' => 'OTP_MAX_ATTEMPTS_EXCEEDED',
                'remaining_attempts' => 0,
                'message' => 'Mã OTP đã bị vô hiệu hóa do nhập sai quá 5 lần. Vui lòng yêu cầu mã mới.',
            ];
        }

        // Đối chiếu mã OTP bằng Hash::check() với chuỗi băm trong Redis
        if (! Hash::check($otpCode, (string) $record['otp_hash'])) {
            $attempts++;
            $record['attempts'] = $attempts;

            // Nếu đã nhập sai đủ 5 lần -> xóa ngay lập tức khỏi Redis
            if ($attempts >= self::MAX_VERIFY_ATTEMPTS) {
                Cache::forget(self::recordKey($phone));
                return [
                    'ok' => false,
                    'status' => 429,
                    'code' => 'OTP_MAX_ATTEMPTS_EXCEEDED',
                    'remaining_attempts' => 0,
                    'message' => 'Bạn đã nhập sai mã OTP 5 lần. Mã OTP đã tự động bị vô hiệu hóa, vui lòng yêu cầu mã mới.',
                ];
            }

            $remainingTtl = max(1, ((int) $record['expires_at']) - $nowTs);
            Cache::put(self::recordKey($phone), $record, $remainingTtl);
            $remainingAttempts = self::MAX_VERIFY_ATTEMPTS - $attempts;

            return [
                'ok' => false,
                'status' => 422,
                'code' => 'OTP_MISMATCH',
                'remaining_attempts' => $remainingAttempts,
                'message' => "Mã OTP không chính xác. Bạn còn {$remainingAttempts} lần thử trước khi mã bị hủy.",
            ];
        }

        // Xác thực thành công -> Vô hiệu hóa (xóa) mã OTP ngay lập tức khỏi Redis để chống dùng lại (Replay Attack)
        Cache::forget(self::recordKey($phone));

        // Cấp token chứng nhận số điện thoại đã xác thực OTP để hoàn tất bước đăng ký tài khoản
        $verificationToken = bin2hex(random_bytes(24));
        Cache::put(self::verifiedKey($phone), [
            'token_hash' => Hash::make($verificationToken),
            'verified_at' => $nowTs,
        ], self::VERIFIED_TOKEN_TTL_SECONDS);

        return [
            'ok' => true,
            'status' => 200,
            'code' => 'OTP_VERIFIED',
            'phone' => $phone,
            'verification_token' => $verificationToken,
            'verified_expires_in' => self::VERIFIED_TOKEN_TTL_SECONDS,
            'message' => "Xác thực mã OTP thành công cho số điện thoại {$phone}.",
        ];
    }

    /**
     * Kiểm tra và tiêu thụ token xác thực SĐT khi tạo tài khoản mới.
     */
    public function consumeVerifiedPhoneToken(string $rawPhone, string $verificationToken): bool
    {
        $phone = SmsGatewayService::normalizeVietnamPhone($rawPhone);
        $verifiedData = Cache::get(self::verifiedKey($phone));

        if (! is_array($verifiedData) || empty($verifiedData['token_hash'])) {
            return false;
        }

        if (! Hash::check($verificationToken, (string) $verifiedData['token_hash'])) {
            return false;
        }

        Cache::forget(self::verifiedKey($phone));
        return true;
    }
}
