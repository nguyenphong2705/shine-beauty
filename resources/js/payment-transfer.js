import axios from 'axios';

/**
 * Module Quản lý Giao diện Thanh toán Chuyển khoản Ngân hàng Tự động (VietQR + SePay Polling 3s)
 * - Không hardcode thông tin ngân hàng ở frontend (tải từ GET /api/orders/{orderCode}/payment).
 * - Tự động Polling mỗi 3 giây qua GET /api/orders/{orderCode}/payment-status bằng Axios.
 * - Đếm ngược thời gian thanh toán (Countdown).
 * - Tự động chuyển sang màn hình Payment Success khi Backend xác nhận PAID.
 * - Tự động chuyển sang màn hình Payment Failed / Expired khi hết hạn hoặc giao dịch lệch tiền.
 */
function formatCurrencyVnd(amount) {
    return new Intl.NumberFormat('vi-VN').format(Number(amount || 0)) + '₫';
}

function formatCountdown(seconds) {
    const s = Math.max(0, Math.floor(Number(seconds || 0)));
    const mins = String(Math.floor(s / 60)).padStart(2, '0');
    const secs = String(s % 60).padStart(2, '0');
    return `${mins}:${secs}`;
}

export function initBankTransferPaymentWidget() {
    const root = document.getElementById('bank-transfer-payment-root');
    if (!root) return;

    const orderCode = root.getAttribute('data-order-code');
    if (!orderCode) return;

    let pollingTimer = null;
    let countdownTimer = null;
    let remainingSeconds = Number(root.getAttribute('data-remaining-seconds') || 900);
    let currentStatus = root.getAttribute('data-payment-status') || 'PENDING_PAYMENT';

    const statePendingEl = document.getElementById('payment-view-pending');
    const stateSuccessEl = document.getElementById('payment-view-success');
    const stateFailedEl = document.getElementById('payment-view-failed');

    const countdownEl = document.getElementById('vietqr-countdown-timer');
    const pollingBadgeEl = document.getElementById('sepay-polling-indicator');
    const qrImageEl = document.getElementById('vietqr-dynamic-image');
    const bankNameEl = document.getElementById('vietqr-bank-name');
    const accountNoEl = document.getElementById('vietqr-account-number');
    const accountNameEl = document.getElementById('vietqr-account-name');
    const amountEl = document.getElementById('vietqr-amount');
    const transferContentEl = document.getElementById('vietqr-transfer-content');
    const orderCodeEls = document.querySelectorAll('[data-bind-order-code]');
    const paidAmountEls = document.querySelectorAll('[data-bind-paid-amount]');
    const paidAtEls = document.querySelectorAll('[data-bind-paid-at]');
    const txCodeEls = document.querySelectorAll('[data-bind-tx-code]');
    const failedReasonEl = document.getElementById('payment-failed-reason');
    const statusBadgeEl = document.getElementById('order-payment-status-badge');

    function stopTimers() {
        if (pollingTimer) {
            clearInterval(pollingTimer);
            pollingTimer = null;
        }
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
    }

    function switchView(viewName) {
        if (statePendingEl) statePendingEl.classList.toggle('hidden', viewName !== 'pending');
        if (stateSuccessEl) stateSuccessEl.classList.toggle('hidden', viewName !== 'success');
        if (stateFailedEl) stateFailedEl.classList.toggle('hidden', viewName !== 'failed');
    }

    function applyPaymentDetails(data) {
        if (!data) return;

        if (data.vietqr) {
            if (qrImageEl && data.vietqr.qr_image_url) {
                qrImageEl.src = data.vietqr.qr_image_url;
                if (data.vietqr.fallback_qr_url) {
                    qrImageEl.onerror = function () {
                        this.onerror = null;
                        this.src = data.vietqr.fallback_qr_url;
                    };
                }
            }
            if (bankNameEl) bankNameEl.textContent = data.vietqr.bank_name || '';
            if (accountNoEl) accountNoEl.textContent = data.vietqr.account_number || '';
            if (accountNameEl) accountNameEl.textContent = data.vietqr.account_name || '';
        }

        if (amountEl) amountEl.textContent = formatCurrencyVnd(data.total_amount);
        if (transferContentEl) transferContentEl.textContent = data.transfer_content || data.order_code;

        orderCodeEls.forEach(el => { el.textContent = data.order_code; });
        paidAmountEls.forEach(el => { el.textContent = formatCurrencyVnd(data.total_amount); });
        if (data.paid_at_formatted) {
            paidAtEls.forEach(el => { el.textContent = data.paid_at_formatted; });
        }
        if (data.transaction_code) {
            txCodeEls.forEach(el => { el.textContent = data.transaction_code; });
        }

        if (typeof data.remaining_seconds === 'number') {
            remainingSeconds = data.remaining_seconds;
            if (countdownEl) countdownEl.textContent = formatCountdown(remainingSeconds);
        }

        handleStatusTransition(data);
    }

    function handleStatusTransition(statusPayload) {
        if (!statusPayload) return;

        currentStatus = statusPayload.payment_status || currentStatus;

        // 1. Trạng thái ĐÃ THANH TOÁN (PAID)
        if (statusPayload.paid === true || currentStatus === 'PAID') {
            stopTimers();
            paidAmountEls.forEach(el => { el.textContent = formatCurrencyVnd(statusPayload.total_amount); });
            if (statusPayload.paid_at_formatted) {
                paidAtEls.forEach(el => { el.textContent = statusPayload.paid_at_formatted; });
            }
            if (statusPayload.transaction_code) {
                txCodeEls.forEach(el => { el.textContent = statusPayload.transaction_code; });
            }
            if (statusBadgeEl) {
                statusBadgeEl.className = 'font-bold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full border border-emerald-300 text-xs';
                statusBadgeEl.textContent = `✓ Đã thanh toán (${statusPayload.transaction_code || 'SePay Verified'})`;
            }
            switchView('success');
            return;
        }

        // 2. Trạng thái HẾT HẠN / LỆCH TIỀN / HỦY (EXPIRED, UNDERPAID, OVERPAID, FAILED, CANCELLED)
        if (
            statusPayload.expired === true ||
            ['EXPIRED', 'UNDERPAID', 'OVERPAID', 'FAILED'].includes(currentStatus) ||
            statusPayload.order_status === 'CANCELLED'
        ) {
            if (currentStatus === 'EXPIRED' || statusPayload.expired === true || statusPayload.order_status === 'CANCELLED') {
                stopTimers();
            }

            if (failedReasonEl) {
                if (currentStatus === 'UNDERPAID') {
                    failedReasonEl.textContent = `Hệ thống ghi nhận số tiền chuyển khoản (${formatCurrencyVnd(statusPayload.received_amount)}) thấp hơn tổng tiền đơn hàng (${formatCurrencyVnd(statusPayload.total_amount)}). Đơn hàng đang chờ Quản trị viên đối soát.`;
                } else if (currentStatus === 'OVERPAID') {
                    failedReasonEl.textContent = `Hệ thống ghi nhận số tiền chuyển khoản (${formatCurrencyVnd(statusPayload.received_amount)}) lớn hơn tổng tiền đơn hàng (${formatCurrencyVnd(statusPayload.total_amount)}). Đơn hàng đang chờ Quản trị viên đối soát và hoàn tiền dư.`;
                } else if (statusPayload.order_status === 'CANCELLED') {
                    failedReasonEl.textContent = 'Đơn hàng này đã bị hủy.';
                } else {
                    failedReasonEl.textContent = 'Đơn hàng đã hết thời gian chờ thanh toán chuyển khoản (15 phút). Nếu bạn đã chuyển khoản, giao dịch sẽ được Quản trị viên đối soát thủ công.';
                }
            }

            if (statusBadgeEl) {
                statusBadgeEl.className = 'font-bold text-red-800 bg-red-100 px-3 py-1 rounded-full border border-red-300 text-xs';
                statusBadgeEl.textContent = statusPayload.payment_status_label || 'Thanh toán chưa hoàn tất / Hết hạn';
            }

            switchView('failed');
            return;
        }

        // 3. Trạng thái ĐANG CHỜ THANH TOÁN (PENDING_PAYMENT)
        switchView('pending');
    }

    async function fetchInitialPaymentInfo() {
        try {
            const response = await axios.get(`/api/orders/${encodeURIComponent(orderCode)}/payment`, {
                headers: { 'Accept': 'application/json' }
            });
            if (response.data && response.data.success && response.data.data) {
                applyPaymentDetails(response.data.data);
            }
        } catch (err) {
            console.warn('Could not fetch initial payment config:', err);
        }
    }

    async function pollPaymentStatus() {
        try {
            if (pollingBadgeEl) {
                pollingBadgeEl.textContent = 'Đang đồng bộ trạng thái thanh toán...';
            }
            const response = await axios.get(`/api/orders/${encodeURIComponent(orderCode)}/payment-status`, {
                headers: { 'Accept': 'application/json' }
            });
            if (response.data && response.data.success) {
                handleStatusTransition(response.data);
            }
            if (pollingBadgeEl && currentStatus === 'PENDING_PAYMENT') {
                pollingBadgeEl.textContent = 'Đang chờ thanh toán • Tự động kiểm tra mỗi 3 giây';
            }
        } catch (err) {
            if (pollingBadgeEl) {
                pollingBadgeEl.textContent = 'Đang chờ thanh toán • Tự động kết nối lại...';
            }
        }
    }

    // Khởi tạo dữ liệu VietQR từ API
    fetchInitialPaymentInfo();

    // Nếu chưa PAID thì bắt đầu Countdown 1 giây và Polling API 3 giây
    if (currentStatus !== 'PAID') {
        countdownTimer = setInterval(() => {
            if (remainingSeconds > 0) {
                remainingSeconds -= 1;
                if (countdownEl) {
                    countdownEl.textContent = formatCountdown(remainingSeconds);
                }
                if (remainingSeconds <= 0) {
                    pollPaymentStatus();
                }
            }
        }, 1000);

        pollingTimer = setInterval(pollPaymentStatus, 3000);
    }

    window.triggerManualStatusCheck = function () {
        pollPaymentStatus();
    };
}

document.addEventListener('DOMContentLoaded', initBankTransferPaymentWidget);
