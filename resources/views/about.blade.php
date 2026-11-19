@extends('layouts.app')

@section('title', 'Về Chúng Tôi | SHINE Beauty Cosmetics')
@section('meta_description', 'Tìm hiểu về SHINE Beauty — Hệ thống phân phối mỹ phẩm cao cấp & chăm sóc da chuẩn khoa học chính hãng 100% từ Pháp, Hàn Quốc và Nhật Bản tại TP Hồ Chí Minh.')
@section('canonical', route('about'))

@section('content')
<div class="space-y-12 sm:space-y-16">
    <!-- 1. HERO BANNER: VỀ SHINE BEAUTY -->
    <section class="relative overflow-hidden bg-gradient-to-br from-sage-100/80 via-[#F5FAF7] to-[#ECF5F0] border-b border-sage-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 lg:py-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                <div class="lg:col-span-7 space-y-5 sm:space-y-6">
                    <div class="inline-flex items-center gap-2 bg-white/90 border border-sage-200 text-sage-600 px-3.5 sm:px-4 py-1.5 rounded-full text-[11px] sm:text-xs font-semibold tracking-wide shadow-2xs">
                        <span>✦ VỀ SHINE BEAUTY COSMETICS</span>
                    </div>

                    <h1 class="font-serif text-3xl sm:text-5xl font-bold text-stone-900 leading-[1.15]">
                        Đánh Thức Vẻ Đẹp <br>
                        <span class="text-sage-500 italic font-normal">Thuần Khiết Chuẩn Khoa Học</span>
                    </h1>

                    <p class="text-stone-600 text-sm sm:text-lg leading-relaxed max-w-2xl">
                        SHINE Beauty ra đời với sứ mệnh mang đến giải pháp chăm sóc da và làm đẹp minh bạch, an toàn và hiệu quả dựa trên khoa học hoạt chất (Niacinamide, BHA, Retinol, Hyaluronic Acid, Vitamin B5) từ những thương hiệu mỹ phẩm chính hãng hàng đầu Pháp, Hàn Quốc và Nhật Bản.
                    </p>

                    <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 sm:gap-4 pt-1">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center gap-2 bg-sage-500 hover:bg-sage-600 text-white font-semibold px-6 sm:px-8 py-3.5 rounded-full shadow-lg shadow-sage-200/90 transition text-sm sm:text-base">
                            <span>Khám Phá Sản Phẩm →</span>
                        </a>
                        <a href="{{ route('blog.index') }}" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-sage-50 text-stone-800 font-semibold px-6 sm:px-7 py-3.5 rounded-full border border-sage-200 transition text-sm sm:text-base">
                            <span>Đọc Blog Làm Đẹp</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 pt-4">
                        <div class="bg-white/90 border border-sage-100 rounded-2xl p-3.5 shadow-2xs">
                            <p class="font-serif text-xl sm:text-2xl font-bold text-sage-600">{{ $totalProductsCount }}+</p>
                            <p class="text-[11px] text-stone-500 mt-0.5">Sản phẩm chính hãng</p>
                        </div>
                        <div class="bg-white/90 border border-sage-100 rounded-2xl p-3.5 shadow-2xs">
                            <p class="font-serif text-xl sm:text-2xl font-bold text-sage-600">{{ $brands->count() }}+</p>
                            <p class="text-[11px] text-stone-500 mt-0.5">Thương hiệu quốc tế</p>
                        </div>
                        <div class="bg-white/90 border border-sage-100 rounded-2xl p-3.5 shadow-2xs">
                            <p class="font-serif text-xl sm:text-2xl font-bold text-sage-600">100%</p>
                            <p class="text-[11px] text-stone-500 mt-0.5">Tem phụ minh bạch</p>
                        </div>
                        <div class="bg-white/90 border border-sage-100 rounded-2xl p-3.5 shadow-2xs">
                            <p class="font-serif text-xl sm:text-2xl font-bold text-sage-600">14 ngày</p>
                            <p class="text-[11px] text-stone-500 mt-0.5">Đổi trả linh hoạt</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="relative rounded-3xl sm:rounded-[2.5rem] overflow-hidden shadow-2xl shadow-sage-200/80 border-4 sm:border-8 border-white aspect-[4/5] max-w-md mx-auto lg:max-w-none">
                        <img src="https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=1000&q=85" alt="Không gian trải nghiệm mỹ phẩm SHINE Beauty" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent flex items-end p-5 sm:p-7">
                            <div class="bg-white/95 backdrop-blur-md p-4 sm:p-5 rounded-2xl w-full shadow-lg space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-sage-600">✦ Cam Kết Từ SHINE Beauty</span>
                                <p class="font-serif font-bold text-stone-800 text-sm sm:text-base">
                                    Mỹ phẩm thật — Kiến thức đúng — Làn da khỏe bền vững
                                </p>
                                <p class="text-xs text-stone-500">
                                    Mỗi sản phẩm đều có hóa đơn, nguồn gốc rõ ràng và được tư vấn theo đúng tình trạng da.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. CÂU CHUYỆN THƯƠNG HIỆU & TRIẾT LÝ CHĂM SÓC DA -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-sage-100 p-6 sm:p-10 lg:p-12 shadow-sm grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            <div class="lg:col-span-5">
                <div class="rounded-3xl overflow-hidden border border-sage-100 aspect-[4/3] bg-sage-50">
                    <img src="https://images.unsplash.com/photo-1612817288484-6f916006741a?auto=format&fit=crop&w=900&q=85" alt="Triết lý chăm sóc da chuẩn khoa học" class="w-full h-full object-cover">
                </div>
            </div>
            <div class="lg:col-span-7 space-y-4">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-sage-500">Câu Chuyện Thương Hiệu</span>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold text-stone-800 leading-snug">
                    Chăm Sóc Da Dựa Trên Khoa Học Hoạt Chất &amp; Sự Minh Bạch Tuyệt Đối
                </h2>
                <p class="text-sm sm:text-base text-stone-600 leading-relaxed">
                    Thay vì chạy theo lời quảng cáo thổi phồng, SHINE Beauty chọn lọc kỹ lưỡng từng công thức mỹ phẩm có bảng thành phần (INCI) rõ ràng, nồng độ hoạt chất đạt chuẩn lâm sàng như <strong>Niacinamide 10%</strong>, <strong>BHA 2% Salicylic Acid</strong>, <strong>Retinol 0.5%</strong>, <strong>Hyaluronic Acid đa tầng</strong> và <strong>Panthenol (Vitamin B5)</strong>.
                </p>
                <p class="text-sm sm:text-base text-stone-600 leading-relaxed">
                    Chúng tôi hiểu rằng mỗi làn da — từ da dầu mụn, da khô thiếu ẩm đến da nhạy cảm dễ kích ứng — đều cần một chu trình tối giản, đúng hoạt chất và được theo dõi sát sao để phục hồi hàng rào bảo vệ da tự nhiên.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <div class="p-3.5 rounded-2xl bg-[#F5FAF7] border border-sage-100">
                        <p class="text-xs font-bold text-sage-700">💧 Da Dầu &amp; Mụn</p>
                        <p class="text-[11px] text-stone-500 mt-1">Kiểm soát bã nhờn, thông thoáng lỗ chân lông với BHA &amp; Niacinamide.</p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#F5FAF7] border border-sage-100">
                        <p class="text-xs font-bold text-sage-700">🌿 Da Khô &amp; Lão Hóa</p>
                        <p class="text-[11px] text-stone-500 mt-1">Cấp ẩm đa tầng và tái tạo bề mặt da với HA, Ceramide &amp; Retinol.</p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#F5FAF7] border border-sage-100">
                        <p class="text-xs font-bold text-sage-700">🌸 Da Nhạy Cảm</p>
                        <p class="text-[11px] text-stone-500 mt-1">Làm dịu đỏ rát tức thì, củng cố màng ẩm với Vitamin B5 &amp; Rau má.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. 4 GIÁ TRỊ CỐT LÕI -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="text-center max-w-2xl mx-auto space-y-2">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-sage-500">Giá Trị Cốt Lõi</span>
            <h2 class="font-serif text-2xl sm:text-3xl font-bold text-stone-800">Điều Làm Nên Uy Tín Của SHINE Beauty</h2>
            <p class="text-xs sm:text-sm text-stone-500">
                Bốn tiêu chuẩn bất biến trong mọi sản phẩm và dịch vụ mà chúng tôi trao đến khách hàng.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
            <div class="bg-white rounded-3xl border border-sage-100 p-6 shadow-xs space-y-2.5">
                <div class="w-11 h-11 rounded-2xl bg-sage-50 border border-sage-100 text-sage-600 flex items-center justify-center text-xl">✓</div>
                <h3 class="font-serif text-lg font-bold text-stone-800">100% Chính Hãng</h3>
                <p class="text-xs sm:text-sm text-stone-500 leading-relaxed">
                    Sản phẩm nhập khẩu chính ngạch từ Pháp, Hàn Quốc, Nhật Bản với đầy đủ tem phụ tiếng Việt và mã truy xuất rõ ràng.
                </p>
            </div>
            <div class="bg-white rounded-3xl border border-sage-100 p-6 shadow-xs space-y-2.5">
                <div class="w-11 h-11 rounded-2xl bg-sage-50 border border-sage-100 text-sage-600 flex items-center justify-center text-xl">🔬</div>
                <h3 class="font-serif text-lg font-bold text-stone-800">Chuẩn Khoa Học Da Liễu</h3>
                <p class="text-xs sm:text-sm text-stone-500 leading-relaxed">
                    Công khai minh bạch bảng thành phần INCI, nồng độ hoạt chất và hướng dẫn kết hợp chu trình an toàn cho từng tình trạng da.
                </p>
            </div>
            <div class="bg-white rounded-3xl border border-sage-100 p-6 shadow-xs space-y-2.5">
                <div class="w-11 h-11 rounded-2xl bg-sage-50 border border-sage-100 text-sage-600 flex items-center justify-center text-xl">💬</div>
                <h3 class="font-serif text-lg font-bold text-stone-800">Tư Vấn Cá Nhân Hóa</h3>
                <p class="text-xs sm:text-sm text-stone-500 leading-relaxed">
                    Đội ngũ tư vấn viên luôn sẵn sàng gợi ý routine phù hợp riêng cho Da dầu, Da khô, Da nhạy cảm và mục tiêu dưỡng da của bạn.
                </p>
            </div>
            <div class="bg-white rounded-3xl border border-sage-100 p-6 shadow-xs space-y-2.5">
                <div class="w-11 h-11 rounded-2xl bg-terracotta-50 border border-terracotta-100 text-terracotta-600 flex items-center justify-center text-xl">🚚</div>
                <h3 class="font-serif text-lg font-bold text-stone-800">Trải Nghiệm Tận Tâm</h3>
                <p class="text-xs sm:text-sm text-stone-500 leading-relaxed">
                    Miễn phí vận chuyển toàn quốc cho đơn từ 900.000₫, hỗ trợ thanh toán COD/VietQR tự động và đổi trả trong 14 ngày.
                </p>
            </div>
        </div>
    </section>

    <!-- 4. THƯƠNG HIỆU ĐỐI TÁC & DANH MỤC NỔI BẬT -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white rounded-3xl border border-sage-100 p-6 sm:p-10 shadow-xs space-y-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 border-b border-sage-100 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-sage-500">Đối Tác &amp; Danh Mục</span>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-stone-800 mt-1">
                        Thương Hiệu Đối Tác &amp; Dòng Sản Phẩm Chủ Lực
                    </h2>
                </div>
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-sage-600 hover:underline whitespace-nowrap">
                    Xem tất cả sản phẩm →
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-5">
                @foreach($brands as $brand)
                    <a href="{{ route('products.index', ['brand' => $brand->id]) }}" class="flex flex-col items-center justify-center p-4 sm:p-5 rounded-2xl bg-[#F5FAF7] hover:bg-sage-50 border border-sage-100 transition group text-center">
                        <span class="font-serif text-base sm:text-lg font-bold text-stone-800 group-hover:text-sage-600">{{ $brand->name }}</span>
                        <span class="text-[11px] sm:text-xs text-stone-500 mt-1">Xuất xứ: {{ $brand->origin }}</span>
                        <span class="text-[11px] font-semibold text-sage-600 mt-1.5">{{ $brand->products_count ?? 0 }} sản phẩm →</span>
                    </a>
                @endforeach
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5 pt-2">
                @foreach($categories as $cat)
                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="group relative h-40 sm:h-48 rounded-2xl overflow-hidden border border-sage-100 shadow-xs">
                        <img src="{{ $cat->image }}" alt="{{ $cat->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950/75 via-stone-900/20 to-transparent flex flex-col justify-end p-4">
                            <h3 class="font-serif text-base sm:text-lg font-bold text-white group-hover:text-sage-200 transition">{{ $cat->name }}</h3>
                            <span class="text-[11px] text-sage-200">{{ $cat->products_count ?? 0 }} sản phẩm →</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 5. SHOWROOM & THÔNG TIN LIÊN HỆ -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-stone-900 text-stone-100 rounded-3xl p-6 sm:p-10 lg:p-12 shadow-xl border border-stone-800 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-7 space-y-4">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-sage-400">Showroom &amp; Hỗ Trợ Khách Hàng</span>
                <h2 class="font-serif text-2xl sm:text-3xl font-bold text-white">
                    Ghé Thăm Cửa Hàng SHINE Beauty Tại TP Hồ Chí Minh
                </h2>
                <p class="text-xs sm:text-sm text-stone-300 leading-relaxed max-w-2xl">
                    Mời bạn ghé trải nghiệm trực tiếp các dòng mỹ phẩm chính hãng, soi da và nhận tư vấn chu trình dưỡng da phù hợp tại showroom của chúng tôi.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2 text-xs sm:text-sm">
                    <div class="p-4 rounded-2xl bg-stone-800/90 border border-stone-700/80 space-y-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-sage-400">📍 Địa chỉ Showroom</span>
                        <p class="font-semibold text-white">Đường Phạm Văn Chiêu, Phường Thông Tây Hội, TP Hồ Chí Minh</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-stone-800/90 border border-stone-700/80 space-y-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-sage-400">📞 Hotline Da Liễu</span>
                        <p class="font-semibold text-white">1900 6868</p>
                        <p class="text-[11px] text-stone-400">Email: no-reply@shinebeauty.vn</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-stone-800/90 border border-stone-700/80 space-y-1 sm:col-span-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-sage-400">🕒 Giờ mở cửa</span>
                        <p class="font-semibold text-white">08:30 – 21:30 (Tất cả các ngày trong tuần, kể cả Thứ 7 &amp; Chủ Nhật)</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 bg-stone-800/70 border border-stone-700 rounded-3xl p-6 space-y-4">
                <h3 class="font-serif text-lg sm:text-xl font-bold text-white">Sẵn Sàng Bắt Đầu Chu Trình Mới?</h3>
                <p class="text-xs sm:text-sm text-stone-300 leading-relaxed">
                    Khám phá ngay bộ sưu tập mỹ phẩm chính hãng hoặc tra cứu nhanh trạng thái đơn hàng của bạn tại SHINE Beauty.
                </p>
                <div class="flex flex-col gap-3 pt-1">
                    <a href="{{ route('products.index') }}" class="w-full inline-flex items-center justify-center bg-sage-500 hover:bg-sage-600 text-white font-semibold px-6 py-3.5 rounded-2xl transition text-sm">
                        🛍️ Mua Sắm Ngay Tại Cửa Hàng →
                    </a>
                    <a href="{{ route('orders.track') }}" class="w-full inline-flex items-center justify-center bg-stone-900 hover:bg-stone-700 text-stone-100 border border-stone-600 font-semibold px-6 py-3.5 rounded-2xl transition text-sm">
                        📦 Tra Cứu Đơn Hàng →
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
