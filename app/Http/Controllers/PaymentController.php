<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
    }

    /**
     * GET /api/orders/{orderCode}/payment
     * Lấy thông tin thanh toán chuyển khoản và mã QR VietQR của đơn hàng.
     */
    public function show(Request $request, string $orderCode): JsonResponse
    {
        $order = OrderController::findOrderByCodeOrId($orderCode);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng.',
            ], 404);
        }

        if (!OrderController::canAccessOrder($request, $order)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập thông tin thanh toán của đơn hàng này.',
            ], 403);
        }

        $payload = $this->paymentService->getOrderPaymentPayload($order);

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }

    /**
     * GET /api/orders/{orderCode}/payment-status
     * Endpoint nhẹ phục vụ Polling mỗi 3 giây kiểm tra trạng thái thanh toán của đơn hàng.
     */
    public function status(Request $request, string $orderCode): JsonResponse
    {
        $order = OrderController::findOrderByCodeOrId($orderCode);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng.',
            ], 404);
        }

        if (!OrderController::canAccessOrder($request, $order)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền kiểm tra trạng thái thanh toán của đơn hàng này.',
            ], 403);
        }

        $payload = $this->paymentService->getOrderPaymentPayload($order);

        return response()->json([
            'success' => true,
            'order_id' => $payload['order_id'],
            'order_code' => $payload['order_code'],
            'paid' => $payload['paid'],
            'expired' => $payload['expired'],
            'payment_status' => $payload['payment_status'],
            'payment_status_label' => $payload['payment_status_label'],
            'order_status' => $payload['order_status'],
            'order_status_label' => $payload['order_status_label'],
            'total_amount' => $payload['total_amount'],
            'received_amount' => $payload['received_amount'],
            'transfer_content' => $payload['transfer_content'],
            'transaction_code' => $payload['transaction_code'],
            'paid_at' => $payload['paid_at'],
            'paid_at_formatted' => $payload['paid_at_formatted'],
            'remaining_seconds' => $payload['remaining_seconds'],
        ]);
    }
}
