<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class SmsGatewayService
 *
 * Dịch vụ tích hợp gửi tin nhắn OTP đa cổng:
 * 1. eSMS.vn (Brandname SMS tại Việt Nam)
 * 2. Zalo ZNS (Zalo Notification Service qua Zalo Official Account)
 * 3. Twilio Programmable Messaging (Quốc tế / SMS)
 */
class SmsGatewayService
{
    /**
     * Chuẩn hóa số điện thoại Việt Nam sang định dạng 0xxxxxxxxx.
     */
    public static function normalizeVietnamPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';
        if (str_starts_with($digits, '84') && strlen($digits) >= 11) {
            return '0' . substr($digits, 2);
        }
        return $digits;
    }

    /**
     * Chuẩn hóa số điện thoại sang định dạng quốc tế E.164 (84xxxxxxxxx hoặc +84xxxxxxxxx).
     */
    public static function toInternationalPhone(string $phone, bool $withPlus = false): string
    {
        $normalized = self::normalizeVietnamPhone($phone);
        if (str_starts_with($normalized, '0')) {
            $intl = '84' . substr($normalized, 1);
        } else {
            $intl = $normalized;
        }
        return $withPlus ? ('+' . $intl) : $intl;
    }

    /**
     * Gửi mã OTP đăng ký tài khoản qua cổng cấu hình (eSMS / Zalo ZNS / Twilio).
     *
     * @param string $phone Số điện thoại người nhận
     * @param string $otpCode Mã OTP 6 chữ số
     * @param string|null $preferredProvider Chỉ định cổng ('esms', 'zalo_zns', 'twilio') hoặc dùng mặc định
     * @return array<string, mixed>
     */
    public function sendRegistrationOtp(string $phone, string $otpCode, ?string $preferredProvider = null): array
    {
        $driver = strtolower($preferredProvider ?: (string) config('services.sms.driver', 'esms'));

        return match ($driver) {
            'zalo_zns' => $this->sendViaZaloZns($phone, $otpCode),
            'twilio'   => $this->sendViaTwilio($phone, $otpCode),
            'esms'     => $this->sendViaEsms($phone, $otpCode),
            default    => $this->sendViaLog($phone, $otpCode, $driver),
        };
    }

    /**
     * 1. Gửi SMS OTP qua eSMS.vn REST API V4.
     * Tài liệu: https://esms.vn/blog/huong-dan-tich-hop-api-esms-vn
     */
    public function sendViaEsms(string $phone, string $otpCode): array
    {
        $cfg = config('services.sms.esms', []);
        $endpoint = $cfg['endpoint'] ?? 'https://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_post_json/';
        $apiKey = (string) ($cfg['api_key'] ?? '');
        $secretKey = (string) ($cfg['secret_key'] ?? '');
        $brandname = (string) ($cfg['brandname'] ?? 'SHINEBEAUTY');
        $smsType = (int) ($cfg['sms_type'] ?? 2);
        $sandbox = (int) ($cfg['sandbox'] ?? 0);

        $normalizedPhone = self::normalizeVietnamPhone($phone);
        $content = "[SHINE Beauty] Ma xac thuc OTP dang ky tai khoan cua ban la {$otpCode}. Ma co hieu luc trong 3 phut. Tuyet doi khong chia se ma nay.";

        if ($apiKey === '' || $secretKey === '') {
            return $this->sendViaLog($normalizedPhone, $otpCode, 'esms');
        }

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->post($endpoint, [
                    'ApiKey'      => $apiKey,
                    'SecretKey'   => $secretKey,
                    'Phone'       => $normalizedPhone,
                    'Content'     => $content,
                    'Brandname'   => $brandname,
                    'SmsType'     => $smsType,
                    'IsUnicode'   => 0,
                    ' Sandbox'    => $sandbox,
                    'RequestId'   => 'OTP-' . now()->format('YmdHis') . '-' . substr(md5($normalizedPhone), 0, 6),
                ]);

            $body = $response->json() ?? [];
            $codeResult = (string) ($body['CodeResult'] ?? '');
            $success = $response->successful() && $codeResult === '100';

            if (! $success) {
                Log::warning('eSMS OTP send failed', [
                    'phone' => $normalizedPhone,
                    'status' => $response->status(),
                    'response' => $body,
                ]);
            }

            return [
                'success' => $success,
                'provider' => 'esms',
                'message_id' => $body['SMSID'] ?? null,
                'code_result' => $codeResult,
                'raw' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('eSMS OTP exception: ' . $e->getMessage(), ['phone' => $normalizedPhone]);
            return [
                'success' => false,
                'provider' => 'esms',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 2. Gửi OTP qua Zalo ZNS (Zalo Notification Service API).
     * Tài liệu: https://developers.zalo.me/docs/zalo-notification-service/gui-thong-bao-zns/gui-zns-su-dung-development-mode-post-5217
     */
    public function sendViaZaloZns(string $phone, string $otpCode): array
    {
        $cfg = config('services.sms.zalo_zns', []);
        $endpoint = $cfg['endpoint'] ?? 'https://business.openapi.zalo.me/message/template';
        $accessToken = (string) ($cfg['access_token'] ?? '');
        $templateId = (string) ($cfg['template_id'] ?? '');
        $intlPhone = self::toInternationalPhone($phone, false); // Định dạng 84xxxxxxxxx cho Zalo ZNS

        if ($accessToken === '' || $templateId === '') {
            return $this->sendViaLog($phone, $otpCode, 'zalo_zns');
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'access_token' => $accessToken,
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, [
                    'phone' => $intlPhone,
                    'template_id' => $templateId,
                    'template_data' => [
                        'otp' => $otpCode,
                        'expire_minutes' => '3',
                    ],
                    'tracking_id' => 'ZNS-OTP-' . now()->format('YmdHis') . '-' . substr(md5($intlPhone), 0, 6),
                ]);

            $body = $response->json() ?? [];
            $errorCode = (int) ($body['error'] ?? -1);
            $success = $response->successful() && $errorCode === 0;

            if (! $success) {
                Log::warning('Zalo ZNS OTP send failed', [
                    'phone' => $intlPhone,
                    'status' => $response->status(),
                    'response' => $body,
                ]);
            }

            return [
                'success' => $success,
                'provider' => 'zalo_zns',
                'message_id' => $body['data']['msg_id'] ?? null,
                'error_code' => $errorCode,
                'raw' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('Zalo ZNS OTP exception: ' . $e->getMessage(), ['phone' => $intlPhone]);
            return [
                'success' => false,
                'provider' => 'zalo_zns',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 3. Gửi SMS OTP qua Twilio Programmable Messaging REST API.
     * Tài liệu: https://www.twilio.com/docs/messaging/api/message-resource#create-a-message-resource
     */
    public function sendViaTwilio(string $phone, string $otpCode): array
    {
        $cfg = config('services.sms.twilio', []);
        $accountSid = (string) ($cfg['account_sid'] ?? '');
        $authToken = (string) ($cfg['auth_token'] ?? '');
        $fromNumber = (string) ($cfg['from'] ?? '');
        $toE164 = self::toInternationalPhone($phone, true); // Định dạng +84xxxxxxxxx

        if ($accountSid === '' || $authToken === '' || $fromNumber === '') {
            return $this->sendViaLog($phone, $otpCode, 'twilio');
        }

        $endpoint = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
        $messageBody = "[SHINE Beauty] Ma OTP dang ky tai khoan cua ban la: {$otpCode}. Ma co hieu luc trong 3 phut.";

        try {
            $response = Http::timeout(8)
                ->withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post($endpoint, [
                    'From' => $fromNumber,
                    'To'   => $toE164,
                    'Body' => $messageBody,
                ]);

            $body = $response->json() ?? [];
            $success = $response->successful() && ! empty($body['sid']);

            if (! $success) {
                Log::warning('Twilio OTP send failed', [
                    'phone' => $toE164,
                    'status' => $response->status(),
                    'response' => $body,
                ]);
            }

            return [
                'success' => $success,
                'provider' => 'twilio',
                'message_id' => $body['sid'] ?? null,
                'raw' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('Twilio OTP exception: ' . $e->getMessage(), ['phone' => $toE164]);
            return [
                'success' => false,
                'provider' => 'twilio',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Chế độ Log / Sandbox khi chưa cấu hình API Key thực tế trên môi trường Local/Dev.
     */
    private function sendViaLog(string $phone, string $otpCode, string $provider): array
    {
        Log::info("[OTP SMS GATEWAY - {$provider}] Gửi mã OTP đăng ký tài khoản", [
            'phone' => self::normalizeVietnamPhone($phone),
            'provider' => $provider,
            'expires_in_seconds' => 180,
            'otp_preview_dev_only' => app()->environment(['local', 'testing']) ? $otpCode : '***REDACTED***',
        ]);

        return [
            'success' => true,
            'provider' => $provider,
            'simulated' => true,
            'message_id' => 'SIM-' . strtoupper($provider) . '-' . now()->format('YmdHis'),
        ];
    }
}
