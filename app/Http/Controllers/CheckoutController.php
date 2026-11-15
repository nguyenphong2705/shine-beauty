<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ApplyCouponRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use RuntimeException;

/**
 * Class CheckoutController
 *
 * Điều hướng và xử lý quy trình thanh toán, áp dụng mã giảm giá, đặt hàng và tra cứu đơn hàng:
 * - Hiển thị trang thanh toán, tính toán tạm tính, phí ship, số tiền giảm voucher.
 * - Áp dụng và gỡ bỏ mã giảm giá (voucher) cho đơn hàng.
 * - Đặt hàng và lưu thông tin đơn hàng cùng các món hàng vào cơ sở dữ liệu.
 * - Tra cứu nhanh lịch sử đơn hàng theo số điện thoại (chống SQL injection và IDOR).
 */
class CheckoutController extends Controller
{
    /** Ngưỡng miễn phí vận chuyển (mặc định: 499.000₫) */
    public const FREE_SHIPPING_THRESHOLD = CheckoutService::FREE_SHIPPING_THRESHOLD;

    /** Phí vận chuyển tiêu chuẩn toàn quốc (mặc định: 30.000₫) */
    public const STANDARD_SHIPPING_FEE = CheckoutService::STANDARD_SHIPPING_FEE;

    /**
     * Khởi tạo Controller với CheckoutService.
     *
     * @param CheckoutService $checkoutService Dịch vụ tính toán đơn hàng và thanh toán
     */
    public function __construct(
        private readonly CheckoutService $checkoutService
    ) {
    }

    /**
     * Hiển thị trang thanh toán và tổng quan đơn hàng.
     *
     * Chức năng:
     * - Lấy giỏ hàng từ session và thông tin mã giảm giá đang áp dụng.
     * - Tính toán chi tiết: tiền hàng, phí ship, số tiền giảm giá, tổng thanh toán.
     * - Tự động gỡ voucher nếu giỏ hàng không còn đủ điều kiện áp dụng.
     * - Hiển thị danh sách các voucher đang kích hoạt để khách hàng lựa chọn.
     * - Bảo vệ chống IDOR: chỉ cho phép xem đơn hàng vừa tạo nếu trùng phiên hoặc đúng user.
     *
     * @param Request $request Yêu cầu HTTP (có thể kèm 'success_order_id')
     * @return View Giao diện thanh toán (checkout.index)
     */
    public function index(Request $request): View
    {
        $cart = session()->get('cart', []);
        $appliedCouponSession = session()->get('applied_coupon');

        $savedAddresses = collect();
        $defaultAddress = null;

        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->ensureAddressesInitialized();
            $savedAddresses = $user->addresses()->get();
            $defaultAddress = $savedAddresses->firstWhere('is_default', true) ?? $savedAddresses->first();
        }

        $initialProvince = $defaultAddress?->province ?: 'TP. Hồ Chí Minh';
        $initialDistrict = $defaultAddress?->district ?: 'Quận 1';

        $summary = $this->checkoutService->calculateCartSummary(
            $cart,
            $appliedCouponSession,
            'ZONE_BASED',
            $initialProvince,
            $initialDistrict
        );

        // Nếu mã giảm giá trong session hết hạn hoặc không đủ điều kiện đơn tối thiểu thì tự động gỡ
        if ($appliedCouponSession && $summary['appliedCoupon'] === null) {
            session()->forget('applied_coupon');
        }

        $activeCoupons = $this->checkoutService->getActiveCoupons();

        // Chống IDOR: Chỉ cho phép xem đơn hàng vừa đặt trong phiên hiện tại hoặc của chính user đang đăng nhập
        $successOrder = null;
        if ($orderId = $request->integer('success_order_id')) {
            $candidateOrder = Order::query()
                ->with(['items.product:id,sku,name,slug,images'])
                ->find($orderId);

            if ($candidateOrder && $this->canAccessOrder($candidateOrder)) {
                $successOrder = $candidateOrder;
            }
        }

        return view('checkout.index', array_merge($summary, [
            'cart' => $cart,
            'activeCoupons' => $activeCoupons,
            'successOrder' => $successOrder,
            'savedAddresses' => $savedAddresses,
            'defaultAddress' => $defaultAddress,
        ]));
    }

    /**
     * Áp dụng mã giảm giá (voucher) vào đơn hàng.
     *
     * Quy tắc nghiệp vụ:
     * - Mỗi đơn hàng chỉ áp dụng tối đa 01 voucher.
     * - Kiểm tra mã có tồn tại, còn hạn sử dụng và đạt giá trị đơn hàng tối thiểu không.
     *
     * @param ApplyCouponRequest $request Form request kiểm tra định dạng mã giảm giá
     * @return RedirectResponse Quay lại trang thanh toán kèm thông báo kết quả
     */
    public function applyCoupon(ApplyCouponRequest $request): RedirectResponse
    {
        $code = $request->validated('coupon_code');
        $existingCoupon = session()->get('applied_coupon');

        // Chỉ cho phép áp dụng tối đa 01 voucher trên mỗi đơn hàng
        if (!empty($existingCoupon['code'])) {
            if ($existingCoupon['code'] === $code) {
                return back()->with('error', "Mã giảm giá {$code} đang được áp dụng trên đơn hàng này.");
            }

            return back()->with(
                'error',
                "Mỗi đơn hàng chỉ được áp dụng tối đa 01 voucher (đang dùng mã {$existingCoupon['code']}). Vui lòng bấm \"Gỡ mã\" hiện tại nếu muốn đổi sang mã {$code}."
            );
        }

        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$coupon || $coupon->isExpired()) {
            return back()->with('error', "Mã giảm giá \"{$code}\" không tồn tại hoặc đã hết hạn.");
        }

        $cart = session()->get('cart', []);
        $subtotal = (int) collect($cart)->sum(
            static fn (array $item): int => (int) $item['price'] * (int) $item['quantity']
        );

        if ($subtotal < (int) ($coupon->min_order_amount ?? 0)) {
            return back()->with(
                'error',
                "Mã giảm giá {$coupon->code} (-{$coupon->discount_percent}%) yêu cầu giá trị đơn hàng tối thiểu " . number_format((int) $coupon->min_order_amount, 0, ',', '.') . '₫.'
            );
        }

        session()->put('applied_coupon', [
            'code' => $coupon->code,
            'discount_percent' => (int) $coupon->discount_percent,
        ]);

        return back()->with('success', "Đã áp dụng mã giảm giá {$coupon->code} (-{$coupon->discount_percent}%)! (Mỗi đơn chỉ áp dụng 01 voucher)");
    }

    /**
     * Gỡ bỏ mã giảm giá đang áp dụng khỏi đơn hàng.
     *
     * @return RedirectResponse Quay lại trang thanh toán kèm thông báo
     */
    public function removeCoupon(): RedirectResponse
    {
        session()->forget('applied_coupon');

        return back()->with('success', 'Đã gỡ mã giảm giá.');
    }

    /**
     * Xử lý xác nhận đặt hàng và lưu đơn hàng vào database.
     *
     * Quy trình xử lý:
     * 1. Kiểm tra giỏ hàng có sản phẩm hay không.
     * 2. Gọi CheckoutService để tạo đơn hàng (Order) và chi tiết đơn hàng (OrderItem) trong Database Transaction.
     * 3. Trừ tồn kho sản phẩm tương ứng.
     * 4. Xóa session giỏ hàng và voucher sau khi đặt hàng thành công.
     * 5. Lưu số điện thoại vào session để hỗ trợ tra cứu đơn và chuyển đến trang thông báo thành công.
     *
     * @param StoreOrderRequest $request Form request xác thực thông tin người nhận (tên, sđt, địa chỉ, phương thức thanh toán)
     * @return RedirectResponse Chuyển hướng đến trang thành công hoặc quay lại nếu có lỗi
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('products.index')
                ->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        try {
            $order = $this->checkoutService->placeOrder(
                $request->validated(),
                $cart,
                session()->get('applied_coupon'),
                Auth::id() ? (int) Auth::id() : null
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        session()->forget(['cart', 'applied_coupon']);
        session()->put('last_customer_phone', $order->phone);

        if ($order->isQrPayment()) {
            return redirect()->route('checkout.index', ['success_order_id' => $order->id])
                ->with(
                    'warning',
                    "Đơn hàng #ORDER-{$order->id} chưa thanh toán thành công. Vui lòng quét mã QR và xác thực đã chuyển khoản thành công để hệ thống xác nhận đã thanh toán cho đơn hàng."
                );
        }

        return redirect()->route('checkout.index', ['success_order_id' => $order->id])
            ->with('success', 'Đặt hàng thành công!');
    }

    /**
     * Kiểm tra quyền truy cập thông tin đơn hàng (chống IDOR).
     */
    private function canAccessOrder(Order $order): bool
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->is_admin || (int) $order->user_id === (int) $user->id) {
                return true;
            }
            if (!empty($user->phone) && $order->phone === $user->phone) {
                return true;
            }
        }

        $lastPhone = session('last_customer_phone');

        return !empty($lastPhone) && $order->phone === $lastPhone;
    }

    /**
     * Kiểm tra trực tuyến trạng thái thanh toán chuyển khoản mã QR của đơn hàng.
     */
    public function checkQrPaymentStatus(Request $request, Order $order): JsonResponse
    {
        if (!$this->canAccessOrder($order)) {
            return response()->json([
                'paid' => false,
                'message' => 'Bạn không có quyền kiểm tra đơn hàng này.',
            ], 403);
        }

        $order->refresh();

        if ($order->isPaid()) {
            return response()->json([
                'paid' => true,
                'payment_status' => 'PAID',
                'payment_status_label' => 'Đã thanh toán cho đơn hàng',
                'transaction_code' => $order->transaction_code,
                'paid_at' => $order->paid_at?->format('d/m/Y H:i:s'),
                'message' => "Đã xác thực chuyển khoản thành công (Mã GD: {$order->transaction_code}). Đơn hàng đã được xác nhận thanh toán!",
            ]);
        }

        return response()->json([
            'paid' => false,
            'payment_status' => 'UNPAID',
            'payment_status_label' => 'Đơn hàng chưa thanh toán thành công',
            'message' => "Chưa xác thực được giao dịch chuyển khoản cho nội dung \"SHINE ORDER{$order->id}\" với số tiền " . number_format((int) $order->total_amount, 0, ',', '.') . '₫. Đơn hàng hiện vẫn ở trạng thái: Đơn hàng chưa thanh toán thành công.',
        ]);
    }

    /**
     * Xác thực giao dịch chuyển khoản qua mã QR trước khi xác nhận đã thanh toán cho đơn hàng.
     */
    public function verifyQrPayment(Request $request, Order $order): RedirectResponse
    {
        if (!$this->canAccessOrder($order)) {
            abort(403, 'Bạn không có quyền xác thực đơn hàng này.');
        }

        if (!$order->isQrPayment()) {
            return redirect()->route('checkout.index', ['success_order_id' => $order->id])
                ->with('error', "Đơn hàng #ORDER-{$order->id} không sử dụng phương thức thanh toán qua mã QR.");
        }

        if ($order->isPaid()) {
            return redirect()->route('checkout.index', ['success_order_id' => $order->id])
                ->with('success', "Đơn hàng #ORDER-{$order->id} đã được xác thực thanh toán trước đó (Mã GD: {$order->transaction_code}).");
        }

        $rawAmountInput = trim((string) $request->input('transfer_amount', ''));
        $normalizedAmountStr = str_starts_with($rawAmountInput, '-')
            ? ''
            : preg_replace('/[^0-9]/', '', $rawAmountInput);

        $validator = Validator::make([
            'transaction_code' => strtoupper(trim((string) $request->input('transaction_code', ''))),
            'transfer_amount' => $normalizedAmountStr,
            'transfer_content' => trim((string) $request->input('transfer_content', '')),
            'confirm_transferred' => $request->input('confirm_transferred'),
        ], [
            'transaction_code' => ['required', 'string', 'regex:/^[A-Za-z0-9\-]{6,30}$/'],
            'transfer_amount' => ['required', 'numeric'],
            'transfer_content' => ['required', 'string', 'max:255'],
            'confirm_transferred' => ['required', 'accepted'],
        ], [
            'transaction_code.required' => 'Vui lòng nhập mã giao dịch chuyển khoản từ ứng dụng Ngân hàng / MoMo.',
            'transaction_code.regex' => 'Mã giao dịch phải gồm 6–30 ký tự chữ, số hoặc dấu gạch ngang (VD: FT2631988899).',
            'transfer_amount.required' => 'Vui lòng nhập chính xác số tiền bạn đã chuyển khoản.',
            'transfer_amount.numeric' => 'Số tiền đã chuyển khoản phải là giá trị số hợp lệ.',
            'transfer_content.required' => 'Vui lòng nhập nội dung chuyển khoản trên biên lai.',
            'confirm_transferred.required' => 'Vui lòng đánh dấu xác nhận bạn đã hoàn tất chuyển khoản.',
            'confirm_transferred.accepted' => 'Vui lòng đánh dấu xác nhận bạn đã hoàn tất chuyển khoản.',
        ]);

        if ($validator->fails()) {
            $order->update(['payment_status' => 'UNPAID']);

            return redirect()->route('checkout.index', ['success_order_id' => $order->id])
                ->withInput()
                ->withErrors($validator)
                ->with('error', $validator->errors()->first() . " Đơn hàng #ORDER-{$order->id} vẫn ở trạng thái: Đơn hàng chưa thanh toán thành công.");
        }

        $txCode = strtoupper(trim((string) $request->input('transaction_code', '')));
        $transferAmount = (int) $normalizedAmountStr;
        $normalizedContent = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $request->input('transfer_content', '')));
        $expectedContent = 'SHINEORDER' . $order->id;
        $failureReason = null;

        if (preg_match('/^0+$/', $txCode)) {
            $failureReason = 'Mã giao dịch không hợp lệ (không được chỉ gồm các chữ số 0).';
        } elseif (
            Order::query()
                ->where('id', '!=', $order->id)
                ->where('payment_status', 'PAID')
                ->where('transaction_code', $txCode)
                ->exists()
        ) {
            $failureReason = "Mã giao dịch {$txCode} đã được sử dụng để xác thực cho một đơn hàng khác.";
        } elseif ($transferAmount !== (int) $order->total_amount) {
            $failureReason = 'Số tiền chuyển khoản (' . number_format($transferAmount, 0, ',', '.') . '₫) không khớp với tổng thanh toán của đơn hàng (' . number_format((int) $order->total_amount, 0, ',', '.') . '₫).';
        } elseif (!str_contains((string) $normalizedContent, $expectedContent)) {
            $failureReason = "Nội dung chuyển khoản chưa chứa đúng mã đối chiếu \"SHINE ORDER{$order->id}\".";
        }

        if ($failureReason !== null) {
            $order->update(['payment_status' => 'UNPAID']);

            return redirect()->route('checkout.index', ['success_order_id' => $order->id])
                ->withInput()
                ->with('error', "Xác thực chuyển khoản thất bại: {$failureReason} Đơn hàng #ORDER-{$order->id} vẫn ở trạng thái: Đơn hàng chưa thanh toán thành công.");
        }

        $order->update([
            'payment_status' => 'PAID',
            'transaction_code' => $txCode,
            'paid_at' => now(),
            'status' => $order->status === 'PENDING' ? 'CONFIRMED' : $order->status,
        ]);

        return redirect()->route('checkout.index', ['success_order_id' => $order->id])
            ->with('success', "Đã xác thực chuyển khoản thành công (Mã GD: {$txCode})! Đơn hàng #ORDER-{$order->id} đã được xác nhận ĐÃ THANH TOÁN.");
    }

    /**
     * Webhook nhận thông báo chuyển khoản tự động từ cổng thanh toán / ngân hàng.
     */
    public function webhookQrPayment(Request $request): JsonResponse
    {
        $content = (string) ($request->input('content') ?? $request->input('description') ?? '');
        $rawAmount = (string) ($request->input('amount') ?? $request->input('transferAmount') ?? '0');
        $amount = str_starts_with(trim($rawAmount), '-')
            ? 0
            : (int) preg_replace('/[^0-9]/', '', $rawAmount);
        $txCode = strtoupper(trim((string) ($request->input('referenceCode') ?? $request->input('transaction_code') ?? '')));

        if (!preg_match('/SHINE\s*ORDER\s*(\d+)/i', $content, $matches)) {
            return response()->json([
                'success' => false,
                'paid' => false,
                'message' => 'Không tìm thấy mã đơn hàng (SHINE ORDER<id>) trong nội dung chuyển khoản.',
            ], 422);
        }

        $orderId = (int) $matches[1];
        $order = Order::query()->find($orderId);

        if (!$order || !$order->isQrPayment()) {
            return response()->json([
                'success' => false,
                'paid' => false,
                'message' => 'Không tìm thấy đơn hàng thanh toán mã QR tương ứng.',
            ], 404);
        }

        if ($amount < (int) $order->total_amount) {
            return response()->json([
                'success' => false,
                'paid' => false,
                'payment_status' => 'UNPAID',
                'message' => 'Số tiền chuyển khoản chưa đủ giá trị đơn hàng. Đơn hàng chưa thanh toán thành công.',
            ], 422);
        }

        if ($txCode === '') {
            $txCode = 'QR-AUTO-' . $order->id . '-' . now()->format('His');
        }

        if (!$order->isPaid()) {
            $order->update([
                'payment_status' => 'PAID',
                'transaction_code' => $txCode,
                'paid_at' => now(),
                'status' => $order->status === 'PENDING' ? 'CONFIRMED' : $order->status,
            ]);
        }

        return response()->json([
            'success' => true,
            'paid' => true,
            'order_id' => $order->id,
            'payment_status' => $order->payment_status,
            'transaction_code' => $order->transaction_code,
            'message' => "Đã xác thực chuyển khoản thành công và xác nhận đã thanh toán cho đơn hàng #ORDER-{$order->id}.",
        ]);
    }

    /**
     * Tra cứu lịch sử đơn hàng nhanh bằng số điện thoại khách hàng.
     *
     * Tính năng an toàn:
     * - Lọc sạch ký tự lạ, ngăn chặn SQL Wildcard Injection (ký tự % hoặc _).
     * - Chỉ tìm kiếm khi số điện thoại có từ 9 chữ số trở lên.
     * - Tự động điền số điện thoại vừa đặt hàng gần nhất từ session nếu có.
     *
     * @param Request $request Yêu cầu HTTP chứa tham số query 'phone'
     * @return View Giao diện tra cứu đơn hàng (checkout.track)
     */
    public function trackOrders(Request $request): View
    {
        $rawPhone = trim((string) $request->query('phone', (string) session('last_customer_phone', '')));
        // Chỉ giữ ký tự số và dấu + để ngăn SQL LIKE Wildcard Injection (% hoặc _)
        $phone = preg_replace('/[^0-9+]/', '', $rawPhone) ?: '';
        $orders = collect();

        if (strlen($phone) >= 9) {
            session()->put('last_customer_phone', $phone);
            $orders = Order::query()
                ->with(['items.product:id,sku,name,slug,images'])
                ->where('phone', $phone)
                ->latest()
                ->limit(20)
                ->get();
        }

        return view('checkout.track', compact('phone', 'orders'));
    }
}
