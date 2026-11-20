{{-- Reusable Confirmation & Notice Modal (SHINE Beauty) --}}
<style>
    @keyframes shineBackdropIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes shineModalPop {
        from { opacity: 0; transform: scale(0.94) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    #shine-confirm-overlay.is-open {
        display: flex;
        animation: shineBackdropIn 0.18s ease-out forwards;
    }
    #shine-confirm-overlay.is-open #shine-confirm-dialog {
        animation: shineModalPop 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>

<div id="shine-confirm-overlay"
     class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-stone-900/55 backdrop-blur-sm"
     role="dialog"
     aria-modal="true"
     aria-labelledby="shine-confirm-title"
     aria-describedby="shine-confirm-message">
    <div id="shine-confirm-dialog"
         class="relative w-full max-w-md bg-white rounded-3xl border border-stone-200/90 shadow-2xl overflow-hidden">
        {{-- Top Accent Bar --}}
        <div id="shine-confirm-accent" class="h-1.5 w-full bg-red-500"></div>

        {{-- Close Button --}}
        <button type="button"
                id="shine-confirm-close"
                aria-label="Đóng"
                class="absolute top-4 right-4 w-8 h-8 rounded-full flex items-center justify-center text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="p-6 sm:p-7">
            {{-- Icon + Title --}}
            <div class="flex items-start gap-4 pr-6">
                <div id="shine-confirm-icon-wrap"
                     class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 border bg-red-50 border-red-100 text-red-600">
                    <svg id="shine-confirm-icon-danger" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <svg id="shine-confirm-icon-warning" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <svg id="shine-confirm-icon-info" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="min-w-0 flex-1 pt-0.5">
                    <span id="shine-confirm-badge"
                          class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full mb-1.5 bg-red-50 text-red-600 border border-red-200">
                        Xác nhận thao tác
                    </span>
                    <h3 id="shine-confirm-title" class="text-lg font-bold text-stone-900 leading-snug">
                        Xác nhận xóa
                    </h3>
                </div>
            </div>

            {{-- Target Item Highlight --}}
            <div id="shine-confirm-item-wrap" class="hidden mt-4 px-3.5 py-2.5 rounded-2xl bg-stone-50 border border-stone-200/80 flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full flex-shrink-0 bg-stone-400" id="shine-confirm-item-dot"></span>
                <span id="shine-confirm-item" class="text-sm font-bold text-stone-800 truncate"></span>
            </div>

            {{-- Main Message --}}
            <p id="shine-confirm-message" class="mt-4 text-sm text-stone-600 leading-relaxed whitespace-pre-line">
                Bạn có chắc chắn muốn thực hiện thao tác này?
            </p>

            {{-- Detail Warning Box --}}
            <div id="shine-confirm-detail-wrap" class="hidden mt-3.5 px-3.5 py-2.5 rounded-2xl bg-red-50/70 border border-red-100 text-xs text-red-700 leading-relaxed flex items-start gap-2">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="shine-confirm-detail"></span>
            </div>

            {{-- Action Buttons --}}
            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                <button type="button"
                        id="shine-confirm-cancel"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-2xl text-sm font-semibold text-stone-700 bg-stone-100 hover:bg-stone-200 border border-stone-200/80 transition cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button"
                        id="shine-confirm-ok"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-2xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 shadow-lg shadow-red-600/20 transition cursor-pointer">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.__shineConfirmInitialized) return;
    window.__shineConfirmInitialized = true;

    const overlay = document.getElementById('shine-confirm-overlay');
    const accent = document.getElementById('shine-confirm-accent');
    const iconWrap = document.getElementById('shine-confirm-icon-wrap');
    const iconDanger = document.getElementById('shine-confirm-icon-danger');
    const iconWarning = document.getElementById('shine-confirm-icon-warning');
    const iconInfo = document.getElementById('shine-confirm-icon-info');
    const badgeEl = document.getElementById('shine-confirm-badge');
    const titleEl = document.getElementById('shine-confirm-title');
    const itemWrap = document.getElementById('shine-confirm-item-wrap');
    const itemDot = document.getElementById('shine-confirm-item-dot');
    const itemEl = document.getElementById('shine-confirm-item');
    const messageEl = document.getElementById('shine-confirm-message');
    const detailWrap = document.getElementById('shine-confirm-detail-wrap');
    const detailEl = document.getElementById('shine-confirm-detail');
    const cancelBtn = document.getElementById('shine-confirm-cancel');
    const okBtn = document.getElementById('shine-confirm-ok');
    const closeBtn = document.getElementById('shine-confirm-close');

    const THEMES = {
        danger: {
            accent: 'h-1.5 w-full bg-red-500',
            iconWrap: 'w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 border bg-red-50 border-red-100 text-red-600',
            badge: 'inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full mb-1.5 bg-red-50 text-red-600 border border-red-200',
            badgeText: 'Cảnh báo xóa / hủy',
            dot: 'w-2 h-2 rounded-full flex-shrink-0 bg-red-500',
            detailWrap: 'mt-3.5 px-3.5 py-2.5 rounded-2xl bg-red-50/70 border border-red-100 text-xs text-red-700 leading-relaxed flex items-start gap-2',
            okBtn: 'w-full sm:w-auto px-5 py-2.5 rounded-2xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 shadow-lg shadow-red-600/20 transition cursor-pointer',
            icon: 'danger'
        },
        warning: {
            accent: 'h-1.5 w-full bg-amber-500',
            iconWrap: 'w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 border bg-amber-50 border-amber-100 text-amber-600',
            badge: 'inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full mb-1.5 bg-amber-50 text-amber-700 border border-amber-200',
            badgeText: 'Lưu ý quan trọng',
            dot: 'w-2 h-2 rounded-full flex-shrink-0 bg-amber-500',
            detailWrap: 'mt-3.5 px-3.5 py-2.5 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-xs text-amber-800 leading-relaxed flex items-start gap-2',
            okBtn: 'w-full sm:w-auto px-5 py-2.5 rounded-2xl text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-lg shadow-amber-600/20 transition cursor-pointer',
            icon: 'warning'
        },
        info: {
            accent: 'h-1.5 w-full bg-emerald-600',
            iconWrap: 'w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 border bg-emerald-50 border-emerald-100 text-emerald-700',
            badge: 'inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full mb-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200',
            badgeText: 'Xác nhận thay đổi',
            dot: 'w-2 h-2 rounded-full flex-shrink-0 bg-emerald-600',
            detailWrap: 'mt-3.5 px-3.5 py-2.5 rounded-2xl bg-emerald-50/70 border border-emerald-100 text-xs text-emerald-800 leading-relaxed flex items-start gap-2',
            okBtn: 'w-full sm:w-auto px-5 py-2.5 rounded-2xl text-sm font-bold text-white bg-emerald-700 hover:bg-emerald-800 shadow-lg shadow-emerald-700/20 transition cursor-pointer',
            icon: 'info'
        }
    };

    let activeResolve = null;

    function closeModal(result) {
        if (!overlay.classList.contains('is-open')) return;
        overlay.classList.remove('is-open');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
        if (activeResolve) {
            const resolve = activeResolve;
            activeResolve = null;
            resolve(result);
        }
    }

    function openModal(options = {}) {
        const type = THEMES[options.type] ? options.type : 'danger';
        const theme = THEMES[type];
        const noticeOnly = Boolean(options.noticeOnly);

        accent.className = theme.accent;
        iconWrap.className = theme.iconWrap;
        badgeEl.className = theme.badge;
        badgeEl.textContent = options.badge || theme.badgeText;
        itemDot.className = theme.dot;
        detailWrap.className = theme.detailWrap;
        okBtn.className = theme.okBtn;

        iconDanger.classList.toggle('hidden', theme.icon !== 'danger');
        iconWarning.classList.toggle('hidden', theme.icon !== 'warning');
        iconInfo.classList.toggle('hidden', theme.icon !== 'info');

        titleEl.textContent = options.title || (noticeOnly ? 'Thông báo' : 'Xác nhận thao tác');
        messageEl.textContent = options.message || 'Bạn có chắc chắn muốn thực hiện thao tác này?';

        if (options.itemLabel) {
            itemEl.textContent = options.itemLabel;
            itemWrap.classList.remove('hidden');
        } else {
            itemWrap.classList.add('hidden');
        }

        if (options.detail) {
            detailEl.textContent = options.detail;
            detailWrap.classList.remove('hidden');
        } else {
            detailWrap.classList.add('hidden');
        }

        cancelBtn.classList.toggle('hidden', noticeOnly);
        cancelBtn.textContent = options.cancelText || 'Hủy bỏ';
        okBtn.textContent = options.confirmText || (noticeOnly ? 'Đã hiểu' : 'Xác nhận');

        overlay.classList.remove('hidden');
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            if (noticeOnly || type === 'info') {
                okBtn.focus();
            } else {
                cancelBtn.focus();
            }
        }, 30);

        return new Promise((resolve) => {
            activeResolve = resolve;
        });
    }

    window.shineConfirm = function (options) {
        return openModal(Object.assign({ noticeOnly: false }, options || {}));
    };

    window.shineNotice = function (options) {
        return openModal(Object.assign({ noticeOnly: true, type: 'warning' }, options || {}));
    };

    okBtn.addEventListener('click', () => closeModal(true));
    cancelBtn.addEventListener('click', () => closeModal(false));
    closeBtn.addEventListener('click', () => closeModal(false));
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal(false);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
            closeModal(false);
        }
    });

    // Declarative form interception: data-confirm & data-confirm-status
    document.addEventListener('submit', async function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.shineConfirmed === '1') {
            delete form.dataset.shineConfirmed;
            return;
        }

        if (form.dataset.confirm === 'true') {
            e.preventDefault();
            const ok = await window.shineConfirm({
                title: form.dataset.confirmTitle || 'Xác nhận thao tác',
                message: form.dataset.confirmMessage || 'Bạn có chắc chắn muốn thực hiện thao tác này?',
                detail: form.dataset.confirmDetail || '',
                itemLabel: form.dataset.confirmItem || '',
                type: form.dataset.confirmType || 'danger',
                confirmText: form.dataset.confirmBtn || 'Xác nhận',
                cancelText: form.dataset.confirmCancel || 'Hủy bỏ'
            });
            if (ok) {
                form.dataset.shineConfirmed = '1';
                form.submit();
            }
            return;
        }

        if (form.dataset.confirmStatus === 'true') {
            e.preventDefault();
            const select = form.querySelector('select[name="status"]');
            const selectedOption = select ? select.options[select.selectedIndex] : null;
            const nextStatus = select ? select.value : '';
            const nextLabel = selectedOption ? selectedOption.text.trim() : nextStatus;
            const orderId = form.dataset.orderId || '';
            const isCancel = nextStatus === 'CANCELLED';

            const ok = await window.shineConfirm({
                title: isCancel ? 'Xác nhận hủy đơn hàng' : 'Cập nhật trạng thái đơn hàng',
                itemLabel: orderId ? ('#ORDER-' + orderId + ' → ' + nextLabel) : nextLabel,
                message: isCancel
                    ? 'Bạn có chắc chắn muốn chuyển đơn hàng này sang trạng thái Hủy đơn?'
                    : 'Xác nhận thay đổi trạng thái đơn hàng sang "' + nextLabel + '"?',
                detail: isCancel ? 'Khách hàng sẽ thấy đơn hàng ở trạng thái đã hủy.' : '',
                type: isCancel ? 'danger' : 'info',
                confirmText: isCancel ? 'Xác nhận hủy đơn' : 'Lưu trạng thái'
            });
            if (ok) {
                form.dataset.shineConfirmed = '1';
                form.submit();
            }
        }
    });
})();
</script>
