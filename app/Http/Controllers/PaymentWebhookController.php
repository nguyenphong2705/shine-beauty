<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SePayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly SePayService $sePayService
    ) {
    }

    /**
     * POST /api/webhooks/sepay
     * Tiếp nhận và xử lý Webhook thông báo biến động số dư từ SePay.
     */
    public function handleSePay(Request $request): JsonResponse
    {
        if (!$this->sePayService->verifyWebhookSignature($request)) {
            Log::warning('Rejected unauthorized SePay webhook request', [
                'ip' => $request->ip(),
                'authorization_present' => $request->hasHeader('Authorization'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized SePay Webhook signature.',
            ], 401);
        }

        try {
            $result = $this->sePayService->processWebhook($request->all());
            $httpCode = (int) ($result['http_code'] ?? 200);
            unset($result['http_code']);

            return response()->json($result, $httpCode);
        } catch (Throwable $e) {
            Log::error('Error processing SePay webhook', [
                'message' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while processing webhook.',
            ], 500);
        }
    }
}
