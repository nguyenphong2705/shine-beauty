<!-- =====================================================================
     SHINE BEAUTY — LOADING ANIMATION (ĐỒNG BỘ TÔNG MÀU SAGE MINT & LOGO VƯƠNG MIỆN VÀNG)
     ===================================================================== -->
<style>
    #shine-top-progress {
        position: fixed;
        top: 0;
        left: 0;
        height: 3px;
        width: 0%;
        z-index: 99999;
        background: linear-gradient(90deg, #4A947A 0%, #E5B246 50%, #2F6250 100%);
        box-shadow: 0 0 10px rgba(74, 148, 122, 0.55), 0 0 4px rgba(229, 178, 70, 0.45);
        transition: width 0.35s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.3s ease;
        pointer-events: none;
    }

    #shine-page-loader {
        position: fixed;
        inset: 0;
        z-index: 99998;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at center, #FFFFFF 0%, #F5FAF7 65%, #DFF2EA 100%);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        opacity: 1;
        visibility: visible;
        transition: opacity 0.38s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.38s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #shine-page-loader.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .shine-loader-emblem {
        position: relative;
        width: 116px;
        height: 116px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Hào quang nền thở nhẹ tông Sage Mint & Vàng Vương Miện */
    .shine-loader-aura {
        position: absolute;
        inset: -10px;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(74, 148, 122, 0.16) 0%, rgba(229, 178, 70, 0.10) 50%, rgba(245, 250, 247, 0) 72%);
        animation: shinePulseAura 2.2s ease-in-out infinite;
    }

    /* Vòng xoay ngoài: Tông xanh Sage Mint chủ đạo (#4A947A) */
    .shine-loader-ring-outer {
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        border: 2.5px solid rgba(74, 148, 122, 0.14);
        border-top-color: #4A947A;
        border-right-color: #68B398;
        animation: shineSpinClockwise 1.35s cubic-bezier(0.55, 0.15, 0.45, 0.85) infinite;
    }

    /* Vòng xoay trong: Tông Vàng Vương Miện (#E5B246 -> #C28822) của Logo */
    .shine-loader-ring-inner {
        position: absolute;
        inset: 9px;
        border-radius: 9999px;
        border: 2px dashed rgba(229, 178, 70, 0.25);
        border-bottom-color: #E5B246;
        border-left-color: #C28822;
        animation: shineSpinCounter 1.9s linear infinite;
    }

    /* Khung tròn chứa Logo cô gái & vương miện vàng */
    .shine-loader-logo-box {
        position: relative;
        width: 78px;
        height: 78px;
        border-radius: 9999px;
        background: linear-gradient(145deg, #FFFFFF 0%, #F2F9F6 100%);
        box-shadow: 0 8px 24px -6px rgba(7, 38, 19, 0.12), inset 0 0 0 1px rgba(74, 148, 122, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: shineFloatLogo 2.2s ease-in-out infinite;
    }

    /* Thanh ánh kim chạy ngang bên dưới tên thương hiệu */
    .shine-loader-bar-track {
        width: 148px;
        height: 4px;
        border-radius: 9999px;
        background: #DFF2EA;
        overflow: hidden;
        position: relative;
    }

    .shine-loader-bar-fill {
        position: absolute;
        top: 0;
        left: -45%;
        width: 45%;
        height: 100%;
        border-radius: 9999px;
        background: linear-gradient(90deg, #4A947A 0%, #E5B246 50%, #4A947A 100%);
        animation: shineSlideBar 1.25s ease-in-out infinite;
    }

    @keyframes shineSpinClockwise {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes shineSpinCounter {
        0% { transform: rotate(360deg); }
        100% { transform: rotate(0deg); }
    }

    @keyframes shinePulseAura {
        0%, 100% { transform: scale(0.94); opacity: 0.7; }
        50% { transform: scale(1.08); opacity: 1; }
    }

    @keyframes shineFloatLogo {
        0%, 100% { transform: translateY(0px) scale(1); }
        50% { transform: translateY(-2.5px) scale(1.03); }
    }

    @keyframes shineSlideBar {
        0% { left: -45%; width: 40%; }
        50% { width: 60%; }
        100% { left: 100%; width: 40%; }
    }

    @media (prefers-reduced-motion: reduce) {
        .shine-loader-ring-outer,
        .shine-loader-ring-inner,
        .shine-loader-aura,
        .shine-loader-logo-box,
        .shine-loader-bar-fill {
            animation-duration: 3s;
        }
    }
</style>

<!-- Thanh tiến trình mảnh trên đỉnh trang -->
<div id="shine-top-progress" aria-hidden="true"></div>

<!-- Màn hình Loading đồng bộ Logo & Tông màu SHINE Beauty -->
<div id="shine-page-loader" role="status" aria-live="polite" aria-label="Đang tải trang SHINE Beauty">
    <div class="shine-loader-emblem">
        <div class="shine-loader-aura"></div>
        <div class="shine-loader-ring-outer"></div>
        <div class="shine-loader-ring-inner"></div>
        <div class="shine-loader-logo-box">
            <img src="{{ asset('logo-menu.svg') }}?v=1"
                 alt="SHINE Beauty Logo"
                 width="52"
                 height="52"
                 class="w-13 h-13 object-contain select-none">
        </div>
    </div>

    <div class="mt-4 text-center select-none">
        <div class="text-xl sm:text-2xl font-bold tracking-tight text-stone-800">
            SHINE <span class="text-sage-500 font-normal italic">Beauty</span>
        </div>
        <p class="text-[10px] uppercase tracking-[0.26em] text-stone-400 font-semibold mt-0.5">
            Luxury Cosmetics
        </p>
    </div>

    <div class="mt-3.5 shine-loader-bar-track">
        <div class="shine-loader-bar-fill"></div>
    </div>
</div>

<script>
    (function () {
        const loader = document.getElementById('shine-page-loader');
        const progress = document.getElementById('shine-top-progress');
        let hideTimer = null;

        function finishLoading() {
            if (progress) {
                progress.style.width = '100%';
                setTimeout(() => {
                    progress.style.opacity = '0';
                    setTimeout(() => { progress.style.width = '0%'; }, 250);
                }, 180);
            }
            if (loader && !loader.classList.contains('is-hidden')) {
                loader.classList.add('is-hidden');
            }
            if (hideTimer) {
                clearTimeout(hideTimer);
                hideTimer = null;
            }
        }

        function startLoading() {
            if (progress) {
                progress.style.opacity = '1';
                progress.style.width = '68%';
            }
            if (loader) {
                loader.classList.remove('is-hidden');
            }
            if (hideTimer) clearTimeout(hideTimer);
            hideTimer = setTimeout(finishLoading, 4000);
        }

        // Bắt đầu thanh tiến trình ngay khi parse DOM
        if (progress) {
            progress.style.opacity = '1';
            progress.style.width = '45%';
        }

        // Ẩn ngay khi DOM sẵn sàng (kèm fallback tối đa 400ms để trang luôn mượt và nhanh)
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            requestAnimationFrame(finishLoading);
        } else {
            document.addEventListener('DOMContentLoaded', () => requestAnimationFrame(finishLoading), { once: true });
            window.addEventListener('load', finishLoading, { once: true });
            setTimeout(finishLoading, 400);
        }

        // Đảm bảo khi nhấn nút Back/Forward trình duyệt không bị kẹt màn hình loading
        window.addEventListener('pageshow', function () {
            finishLoading();
        });

        // Kích hoạt hiệu ứng chuyển trang mượt khi click liên kết nội bộ
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[href]');
            if (!link) return;

            const href = link.getAttribute('href') || '';
            if (
                e.defaultPrevented ||
                e.button !== 0 ||
                e.metaKey || e.ctrlKey || e.shiftKey || e.altKey ||
                link.hasAttribute('download') ||
                link.hasAttribute('data-no-loader') ||
                link.hasAttribute('data-filter-reset') ||
                (link.target && link.target !== '_self') ||
                href.startsWith('#') ||
                href.startsWith('javascript:') ||
                href.startsWith('mailto:') ||
                href.startsWith('tel:')
            ) {
                return;
            }

            try {
                const targetUrl = new URL(link.href, window.location.origin);
                if (targetUrl.origin !== window.location.origin) return;
                if (targetUrl.pathname === window.location.pathname && targetUrl.search === window.location.search && targetUrl.hash !== '') {
                    return;
                }
                startLoading();
            } catch (_) {}
        });

        // Kích hoạt khi submit form chuyển trang (như tìm kiếm, lọc bài viết, thanh toán)
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || e.defaultPrevented || form.hasAttribute('data-no-loader')) return;
            if (form.id === 'product-filter-form') return; // Đã có xử lý AJAX riêng
            startLoading();
        });

        window.ShineLoader = {
            show: startLoading,
            hide: finishLoading
        };
    })();
</script>
