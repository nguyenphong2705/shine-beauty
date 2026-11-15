<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly PaymentService $paymentService
    ) {
    }

    /**
     * POST /api/orders
     * Tạo đơn hàng mới từ danh sách sản phẩm (hoặc giỏ hàng session),
     * luôn tính tổng tiền từ đơn giá sản phẩm trong Database, không tin số tiền từ frontend.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{9,15}$/'],
            'address' => ['required', 'string', 'max:500'],
            'province' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'shipping_mode' => ['nullable', 'string', 'in:FLAT_RATE,ZONE_BASED'],
            'note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:BANK_TRANSFER,COD,MOMO_QR'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1', 'max:99'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Lấy danh sách sản phẩm từ request items hoặc từ session cart
        $cart = [];
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $cart[] = [
                    'id' => (int) $item['product_id'],
                    'quantity' => (int) $item['quantity'],
                    // Giá bên dưới sẽ bị CheckoutService::placeOrder ghi đè bằng effective_price từ DB
                    'price' => 0,
                ];
            }
        } elseif ($request->hasSession()) {
            $cart = array_values($request->session()->get('cart', []));
        }

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng trống, không thể tạo đơn hàng.',
            ], 422);
        }

        $couponSession = null;
        if (!empty($validated['coupon_code'])) {
            $couponSession = ['code' => strtoupper(trim((string) $validated['coupon_code']))];
        } elseif ($request->hasSession()) {
            $couponSession = $request->session()->get('applied_coupon');
        }

        try {
            $order = $this->checkoutService->placeOrder([
                'customer_name' => $validated['customer_name'],
                'phone' => preg_replace('/[^0-9+]/', '', (string) $validated['phone']) ?: $validated['phone'],
                'full_address' => $validated['address'],
                'province' => $validated['province'] ?? 'TP. Hồ Chí Minh',
                'district' => $validated['district'] ?? 'Quận 1',
                'shipping_mode' => $validated['shipping_mode'] ?? 'ZONE_BASED',
                'note' => $validated['note'] ?? null,
                'payment_method' => $validated['payment_method'],
            ], $cart, $couponSession, Auth::id() ? (int) Auth::id() : null);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        if ($request->hasSession()) {
            $request->session()->forget(['cart', 'applied_coupon']);
            $request->session()->put('last_customer_phone', $order->phone);
        }

        $paymentPayload = $this->paymentService->getOrderPaymentPayload($order);

        return response()->json([
            'success' => true,
            'message' => 'Tạo đơn hàng thành công.',
            'data' => [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'total_amount' => (int) $order->total_amount,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'transfer_content' => $paymentPayload['transfer_content'],
                'payment' => $paymentPayload,
            ],
        ], 201);
    }

    /**
     * GET /api/orders/{orderCode}
     * Lấy chi tiết đơn hàng theo mã order_code (hoặc ID), có kiểm tra quyền truy cập chống IDOR.
     */
    public function show(Request $request, string $orderCode): JsonResponse
    {
        $order = $this->findOrderByCodeOrId($orderCode);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng.',
            ], 404);
        }

        if (!$this->canAccessOrder($request, $order)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập đơn hàng của khách hàng khác.',
            ], 403);
        }

        $order = $this->paymentService->syncExpiredPaymentIfNeeded($order);
        $order->loadMissing(['items.product:id,sku,name,slug,images,price,sale_price', 'payment']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'user_id' => $order->user_id,
                'customer_name' => $order->customer_name,
                'phone' => $order->phone,
                'address' => $order->address,
                'note' => $order->note,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'payment_status_label' => $order->payment_status_label,
                'order_status' => $order->order_status ?: $order->status,
                'order_status_label' => $order->display_status_label,
                'transaction_code' => $order->transaction_code,
                'transfer_content' => $order->transfer_content,
                'total_amount' => (int) $order->total_amount,
                'discount_amount' => (int) ($order->discount_amount ?? 0),
                'coupon_code' => $order->coupon_code,
                'paid_at' => $order->paid_at?->toIso8601String(),
                'payment_expires_at' => $order->payment_expires_at?->toIso8601String(),
                'created_at' => $order->created_at?->toIso8601String(),
                'items' => $order->items->map(static fn ($item): array => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'sku' => $item->product?->sku,
                    'quantity' => (int) $item->quantity,
                    'price' => (int) $item->price,
                    'subtotal' => (int) $item->price * (int) $item->quantity,
                ])->values(),
            ],
        ]);
    }

    public static function findOrderByCodeOrId(string $identifier): ?Order
    {
        $clean = trim($identifier);
        if ($clean === '') {
            return null;
        }

        $byCode = Order::query()->whereRaw('UPPER(order_code) = ?', [strtoupper($clean)])->first();
        if ($byCode) {
            return $byCode;
        }

        if (ctype_digit($clean)) {
            return Order::query()->find((int) $clean);
        }

        if (preg_match('/^#?ORDER[\-\s]*(\d+)$/i', $clean, $m)) {
            return Order::query()->find((int) $m[1]);
        }

        return null;
    }

    /**
     * Kiểm tra quyền truy cập đơn hàng (Chống IDOR - Insecure Direct Object Reference).
     */
    public static function canAccessOrder(Request $request, Order $order): bool
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->is_admin) {
                return true;
            }
            if ($order->user_id !== null) {
                return (int) $order->user_id === (int) $user->id;
            }
            if (!empty($user->phone) && $order->phone === $user->phone) {
                return true;
            }
        }

        // Nếu đơn hàng đã gắn với một tài khoản user_id xác định mà người gọi là khách vãng lai hoặc user khác -> từ chối
        if ($order->user_id !== null) {
            return false;
        }

        $sessionPhone = $request->hasSession() ? (string) $request->session()->get('last_customer_phone', '') : '';
        $requestPhone = trim((string) ($request->query('phone') ?? $request->header('X-Customer-Phone') ?? ''));

        if ($sessionPhone !== '' && $order->phone === $sessionPhone) {
            return true;
        }

        if ($requestPhone !== '' && $order->phone === $requestPhone) {
            return true;
        }

        return false;
    }
}
