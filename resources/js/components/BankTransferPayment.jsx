import React, { useEffect, useState, useCallback } from 'react';
import axios from 'axios';

/**
 * ReactJS Component: Hệ thống Thanh toán Chuyển khoản Ngân hàng Tự động (VietQR + SePay Webhook Polling)
 * Bao gồm 4 màn hình:
 * A. Checkout
 * B. Bank Transfer Payment (VietQR + Copy STK/Nội dung + Countdown + Polling 3s)
 * C. Payment Success
 * D. Payment Failed / Expired
 */
export default function BankTransferPayment({ initialOrderCode }) {
    const [payment, setPayment] = useState(null);
    const [loading, setLoading] = useState(true);
    const [remainingSeconds, setRemainingSeconds] = useState(900);
    const [copiedField, setCopiedField] = useState(null);

    const formatVnd = (val) => new Intl.NumberFormat('vi-VN').format(Number(val || 0)) + '₫';

    const formatCountdown = (sec) => {
        const s = Math.max(0, Math.floor(Number(sec || 0)));
        const m = String(Math.floor(s / 60)).padStart(2, '0');
        const r = String(s % 60).padStart(2, '0');
        return `${m}:${r}`;
    };

    const fetchPaymentDetails = useCallback(async () => {
        if (!initialOrderCode) return;
        try {
            const res = await axios.get(`/api/orders/${encodeURIComponent(initialOrderCode)}/payment`);
            if (res.data?.success && res.data?.data) {
                setPayment(res.data.data);
                setRemainingSeconds(res.data.data.remaining_seconds ?? 0);
            }
        } finally {
            setLoading(false);
        }
    }, [initialOrderCode]);

    // Tải thông tin VietQR từ Backend
    useEffect(() => {
        fetchPaymentDetails();
    }, [fetchPaymentDetails]);

    // Polling trạng thái mỗi 3 giây bằng Axios
    useEffect(() => {
        if (!initialOrderCode || !payment || payment.paid || payment.expired) {
            return undefined;
        }

        const pollInterval = setInterval(async () => {
            try {
                const res = await axios.get(`/api/orders/${encodeURIComponent(initialOrderCode)}/payment-status`);
                if (res.data?.success) {
                    setPayment((prev) => ({ ...prev, ...res.data }));
                    if (typeof res.data.remaining_seconds === 'number') {
                        setRemainingSeconds(res.data.remaining_seconds);
                    }
                }
            } catch (_) {}
        }, 3000);

        const countdownInterval = setInterval(() => {
            setRemainingSeconds((prev) => Math.max(0, prev - 1));
        }, 1000);

        return () => {
            clearInterval(pollInterval);
            clearInterval(countdownInterval);
        };
    }, [initialOrderCode, payment?.paid, payment?.expired]);

    const copyToClipboard = (text, fieldName) => {
        if (!text) return;
        navigator.clipboard.writeText(String(text));
        setCopiedField(fieldName);
        setTimeout(() => setCopiedField(null), 1800);
    };

    if (loading) {
        return <div className="p-8 text-center text-sm text-stone-500">Đang tải thông tin thanh toán VietQR...</div>;
    }

    if (!payment) {
        return <div className="p-8 text-center text-sm text-red-600">Không tìm thấy thông tin thanh toán của đơn hàng.</div>;
    }

    // C. Giao diện Payment Success
    if (payment.paid || payment.payment_status === 'PAID') {
        return (
            <div className="bg-emerald-50 border-2 border-emerald-200 rounded-3xl p-8 text-center space-y-5">
                <div className="w-20 h-20 mx-auto rounded-full bg-emerald-600 text-white flex items-center justify-center text-4xl font-bold shadow-md">
                    ✓
                </div>
                <h2 className="text-2xl font-bold text-stone-800">Thanh Toán Chuyển Khoản Thành Công!</h2>
                <div className="bg-white rounded-2xl p-5 border border-emerald-100 text-left text-sm space-y-2 max-w-md mx-auto">
                    <div className="flex justify-between">
                        <span className="text-stone-500">Mã đơn hàng:</span>
                        <strong className="font-mono">{payment.order_code}</strong>
                    </div>
                    <div className="flex justify-between">
                        <span className="text-stone-500">Số tiền đã thanh toán:</span>
                        <strong className="text-emerald-700">{formatVnd(payment.total_amount)}</strong>
                    </div>
                    <div className="flex justify-between">
                        <span className="text-stone-500">Thời gian thanh toán:</span>
                        <strong>{payment.paid_at_formatted}</strong>
                    </div>
                </div>
                <div className="flex justify-center gap-3 pt-2">
                    <a href={`/tra-cuu-don-hang?phone=${encodeURIComponent(payment.phone || '')}`} className="px-6 py-3 rounded-full bg-stone-900 text-white text-xs font-bold">
                        Xem chi tiết đơn hàng
                    </a>
                    <a href="/san-pham" className="px-6 py-3 rounded-full bg-sage-500 text-white text-xs font-bold">
                        Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        );
    }

    // D. Giao diện Payment Failed / Expired
    if (payment.expired || ['EXPIRED', 'UNDERPAID', 'OVERPAID', 'FAILED'].includes(payment.payment_status)) {
        return (
            <div className="bg-red-50 border-2 border-red-200 rounded-3xl p-8 text-center space-y-4">
                <div className="w-16 h-16 mx-auto rounded-full bg-red-600 text-white flex items-center justify-center text-2xl font-bold">
                    !
                </div>
                <h2 className="text-xl font-bold text-stone-800">Giao Dịch Chưa Hoàn Tất Hoặc Đã Hết Hạn</h2>
                <p className="text-xs text-stone-600 max-w-lg mx-auto">{payment.payment_status_label}</p>
            </div>
        );
    }

    // B. Giao diện Bank Transfer Payment (Đang chờ thanh toán)
    const vietqr = payment.vietqr || {};
    return (
        <div className="bg-blue-50/70 border-2 border-blue-200 rounded-3xl p-6 space-y-5">
            <div className="flex items-center justify-between border-b border-blue-100 pb-3">
                <div>
                    <span className="text-xs font-bold uppercase text-blue-600">VietQR Tự Động 24/7 (SePay)</span>
                    <h3 className="text-lg font-bold text-stone-800">Quét Mã QR Ngân Hàng Để Thanh Toán</h3>
                </div>
                <div className="text-right">
                    <span className="text-[11px] text-stone-500 block">Thời gian còn lại</span>
                    <strong className="font-mono text-base text-amber-700">{formatCountdown(remainingSeconds)}</strong>
                </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-12 gap-5 items-center">
                <div className="sm:col-span-5 flex flex-col items-center bg-white p-4 rounded-2xl border border-blue-100">
                    <img src={vietqr.qr_image_url} alt="VietQR" className="w-48 h-48 object-contain" />
                </div>
                <div className="sm:col-span-7 space-y-2.5 text-xs">
                    <div className="bg-white p-3 rounded-xl border border-blue-100">
                        <span className="text-stone-400 block">Ngân hàng:</span>
                        <strong className="text-sm text-stone-800">{vietqr.bank_name}</strong>
                    </div>
                    <div className="bg-white p-3 rounded-xl border border-blue-100 flex items-center justify-between">
                        <div>
                            <span className="text-stone-400 block">Số tài khoản ({vietqr.account_name}):</span>
                            <strong className="font-mono text-sm text-blue-700">{vietqr.account_number}</strong>
                        </div>
                        <button type="button" onClick={() => copyToClipboard(vietqr.account_number, 'acc')} className="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 font-bold">
                            {copiedField === 'acc' ? '✓ Đã chép' : 'Sao chép STK'}
                        </button>
                    </div>
                    <div className="bg-white p-3 rounded-xl border border-blue-100 flex items-center justify-between">
                        <div>
                            <span className="text-stone-400 block">Nội dung chuyển khoản (Mã đơn {payment.order_code}):</span>
                            <strong className="font-mono text-sm text-stone-900">{payment.transfer_content}</strong>
                        </div>
                        <button type="button" onClick={() => copyToClipboard(payment.transfer_content, 'content')} className="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 font-bold">
                            {copiedField === 'content' ? '✓ Đã chép' : 'Sao chép nội dung'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
