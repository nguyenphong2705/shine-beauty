<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class BlogController extends Controller
{
    public const FALLBACK_BLOG_IMAGE = 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=900&q=80';

    /**
     * Danh sách 12 URL hình ảnh mỹ phẩm & chăm sóc da đã xác thực hoạt động 100%.
     *
     * @var array<int, string>
     */
    private const VERIFIED_IMAGES = [
        'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1612817288484-6f916006741a?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1617897903246-719242758050?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=80',
    ];

    /**
     * Đảm bảo URL hình ảnh bài viết Blog luôn hợp lệ và không bị lỗi 404.
     */
    public static function sanitizeBlogImage(?string $url): string
    {
        if (!$url || !in_array($url, self::VERIFIED_IMAGES, true)) {
            return self::FALLBACK_BLOG_IMAGE;
        }

        return $url;
    }

    /**
     * Danh sách các chuyên mục con trong Blog Mỹ Phẩm & Chăm Sóc Da.
     *
     * @return array<string, string>
     */
    private function getTopics(): array
    {
        return [
            'cham-soc-da-khoa-hoc' => 'Chăm Sóc Da Khoa Học',
            'hoat-chat-duong-da' => 'Hoạt Chất Dưỡng Da',
            'review-my-pham' => 'Review Mỹ Phẩm',
            'trang-diem-chong-nang' => 'Trang Điểm & Chống Nắng',
        ];
    }

    /**
     * Kho 28 bài viết chuyên sâu về các sản phẩm mỹ phẩm và chăm sóc da.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getAllPosts(): array
    {
        $rawPosts = [
            // 1
            [
                'slug' => 'quy-trinh-cham-soc-da-khoa-hoc-sang-toi-cho-moi-loai-da',
                'title' => 'Chu Trình Chăm Sóc Da Chuẩn Khoa Học Sáng & Tối Cho Làn Da Căng Bóng, Khỏe Mạnh',
                'category_slug' => 'cham-soc-da-khoa-hoc',
                'category' => 'Chăm Sóc Da Khoa Học',
                'skin_type' => 'Mọi loại da',
                'published_at' => '15/11/2026',
                'published_date' => '2026-11-15',
                'views' => 9840,
                'comments_count' => 142,
                'read_time' => '6 phút đọc',
                'author' => 'BS. Da Liễu Minh Châu',
                'author_role' => 'Chuyên gia Da liễu SHINE Beauty',
                'image' => self::VERIFIED_IMAGES[0],
                'excerpt' => 'Hướng dẫn chi tiết thứ tự thoa mỹ phẩm sáng và tối giúp tối ưu hiệu quả hấp thụ Serum Hyaluronic Acid, Niacinamide, Vitamin C và kem chống nắng phổ rộng mà không gây bí tắc lỗ chân lông.',
                'tags' => ['Skincare Routine', 'Hyaluronic Acid', 'Kem Chống Nắng'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 2
            [
                'slug' => 'ket-hop-niacinamide-bha-va-vitamin-b5-cho-da-dau-mun-lo-chan-long-to',
                'title' => 'Bí Quyết Kết Hợp Niacinamide 10%, BHA 2% & Vitamin B5 Cho Da Dầu Mụn, Lỗ Chân Lông To',
                'category_slug' => 'hoat-chat-duong-da',
                'category' => 'Hoạt Chất Dưỡng Da (BHA, Retinol, B5)',
                'skin_type' => 'Da dầu',
                'published_at' => '14/11/2026',
                'published_date' => '2026-11-14',
                'views' => 9420,
                'comments_count' => 168,
                'read_time' => '7 phút đọc',
                'author' => 'Dược sĩ Thanh Tuyền',
                'author_role' => 'Cố vấn Hoạt chất Mỹ phẩm',
                'image' => self::VERIFIED_IMAGES[1],
                'excerpt' => 'Phân tích cơ chế hiệp đồng giữa Salicylic Acid (BHA 2%), Niacinamide 10% + Zinc 1% và Panthenol (Vitamin B5) giúp giải phóng bã nhờn, thu nhỏ lỗ chân lông và phục hồi da không bong tróc.',
                'tags' => ['Niacinamide 10%', 'BHA 2%', 'Vitamin B5', 'Da Dầu Mụn'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 3
            [
                'slug' => 'top-5-san-pham-cap-am-phuc-hoi-da-kho-nhay-cam-tot-nhat-2026',
                'title' => 'Review Top 5 Mỹ Phẩm Cấp Ẩm Đa Tầng & Phục Hồi Da Khô Nhạy Cảm Đáng Đầu Tư Nhất 2026',
                'category_slug' => 'review-my-pham',
                'category' => 'Review Sản Phẩm Mỹ Phẩm',
                'skin_type' => 'Da khô',
                'published_at' => '13/11/2026',
                'published_date' => '2026-11-13',
                'views' => 8650,
                'comments_count' => 129,
                'read_time' => '5 phút đọc',
                'author' => 'Beauty Editor Lan Anh',
                'author_role' => 'Biên tập viên Làm đẹp SHINE',
                'image' => self::VERIFIED_IMAGES[2],
                'excerpt' => 'Đánh giá chi tiết bảng thành phần, kết cấu và độ thẩm thấu của các dòng tinh chất Hyaluronic Acid, kem dưỡng Ceramide, mặt nạ ngủ và tinh dầu dưỡng cho làn da khô thiếu ẩm.',
                'tags' => ['Review Mỹ Phẩm', 'Da Khô Nhạy Cảm', 'Ceramide'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 4
            [
                'slug' => 'huong-dan-chon-kem-chong-nang-va-trang-diem-lau-troi-khong-gay-mun',
                'title' => 'Cách Chọn Kem Chống Nắng Phổ Rộng & Lớp Nền Trang Điểm Mỏng Nhẹ Không Gây Bí Da',
                'category_slug' => 'trang-diem-chong-nang',
                'category' => 'Trang Điểm & Chống Nắng Bảo Vệ Da',
                'skin_type' => 'Mọi loại da',
                'published_at' => '12/11/2026',
                'published_date' => '2026-11-12',
                'views' => 7890,
                'comments_count' => 95,
                'read_time' => '5 phút đọc',
                'author' => 'Chuyên gia Makeup Bảo Ngọc',
                'author_role' => 'Giám đốc Sáng tạo SHINE Studio',
                'image' => self::VERIFIED_IMAGES[6],
                'excerpt' => 'Khám phá bí quyết kết hợp kem chống nắng màng lọc thế hệ mới cùng Cushion, phấn phủ kiềm dầu và son dưỡng có màu giúp gương mặt rạng rỡ suốt 12 giờ mà vẫn an toàn cho làn da.',
                'tags' => ['Kem Chống Nắng', 'Trang Điểm Tự Nhiên', 'Cushion'],
                'related_category_slug' => 'trang-diem',
            ],
            // 5
            [
                'slug' => 'cham-soc-da-toan-than-va-bi-quyet-luu-huong-nuoc-hoa-suot-ca-ngay',
                'title' => 'Quy Trình Dưỡng Sáng Da Toàn Thân (Body Care) & Mẹo Giữ Mùi Nước Hoa Lưu Hương 12 Giờ',
                'category_slug' => 'cham-soc-da-khoa-hoc',
                'category' => 'Chăm Sóc Da Khoa Học',
                'skin_type' => 'Mọi loại da',
                'published_at' => '11/11/2026',
                'published_date' => '2026-11-11',
                'views' => 7120,
                'comments_count' => 84,
                'read_time' => '6 phút đọc',
                'author' => 'Chuyên gia Hương thơm Hoàng Quân',
                'author_role' => 'Chuyên gia Body Care & Fragrance',
                'image' => self::VERIFIED_IMAGES[10],
                'excerpt' => 'Sự kết hợp hoàn hảo giữa tẩy tế bào chết body, sữa dưỡng thể Niacinamide/Bơ hạt mỡ và kỹ thuật xịt nước hoa trên nền da đủ ẩm giúp cơ thể luôn mềm mịn và tỏa hương tinh tế.',
                'tags' => ['Chăm Sóc Cơ Thể', 'Nước Hoa Chính Hãng', 'Dưỡng Thể'],
                'related_category_slug' => 'cham-soc-co-the',
            ],
            // 6
            [
                'slug' => 'chong-lao-hoa-som-voi-retinol-va-peptide-cho-nguoi-moi-bat-dau',
                'title' => 'Cẩm Nang Chống Lão Hóa Sớm Với Retinol 0.5% & Peptide Cho Người Mới Bắt Đầu',
                'category_slug' => 'hoat-chat-duong-da',
                'category' => 'Hoạt Chất Dưỡng Da (BHA, Retinol, B5)',
                'skin_type' => 'Da nhạy cảm',
                'published_at' => '10/11/2026',
                'published_date' => '2026-11-10',
                'views' => 8930,
                'comments_count' => 154,
                'read_time' => '8 phút đọc',
                'author' => 'BS. Da Liễu Minh Châu',
                'author_role' => 'Chuyên gia Da liễu SHINE Beauty',
                'image' => self::VERIFIED_IMAGES[3],
                'excerpt' => 'Tất tần tật về phương pháp "Kẹp bánh mì" (Sandwich Method) khi dùng Retinol nồng độ thấp kết hợp Peptide và kem dưỡng phục hồi giúp làm mờ nếp nhăn mảnh, tăng sinh collagen an toàn.',
                'tags' => ['Retinol 0.5%', 'Peptide', 'Chống Lão Hóa'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 7
            [
                'slug' => 'review-kem-duong-phuc-hoi-la-roche-posay-cicaplast-baume-b5',
                'title' => 'Review Chi Tiết Kem Dưỡng La Roche-Posay Cicaplast Baume B5+: "Bảo Bối" Cấp Cứu Da Kích Ứng',
                'category_slug' => 'review-my-pham',
                'category' => 'Review Sản Phẩm Mỹ Phẩm',
                'skin_type' => 'Da nhạy cảm',
                'published_at' => '09/11/2026',
                'published_date' => '2026-11-09',
                'views' => 9150,
                'comments_count' => 137,
                'read_time' => '6 phút đọc',
                'author' => 'Dược sĩ Thanh Tuyền',
                'author_role' => 'Cố vấn Hoạt chất Mỹ phẩm',
                'image' => self::VERIFIED_IMAGES[5],
                'excerpt' => 'Phân tích công thức 5% Panthenol kết hợp Madecassoside từ rau má và phức hợp lợi khuẩn Tribioma trong kem dưỡng B5+ giúp làm dịu tức thì làn da đỏ rát sau nặn mụn hoặc peel da.',
                'tags' => ['Cicaplast B5+', 'La Roche-Posay', 'Da Nhạy Cảm'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 8
            [
                'slug' => 'double-cleansing-lam-sach-kep-dung-cach-ngan-ngua-mun-an',
                'title' => 'Double Cleansing (Làm Sạch Kép) Đúng Chuẩn Da Liễu Để Ngăn Ngừa Mụn Ẩn Tận Gốc',
                'category_slug' => 'cham-soc-da-khoa-hoc',
                'category' => 'Chăm Sóc Da Khoa Học',
                'skin_type' => 'Da dầu',
                'published_at' => '08/11/2026',
                'published_date' => '2026-11-08',
                'views' => 6450,
                'comments_count' => 76,
                'read_time' => '5 phút đọc',
                'author' => 'BS. Da Liễu Minh Châu',
                'author_role' => 'Chuyên gia Da liễu SHINE Beauty',
                'image' => self::VERIFIED_IMAGES[0],
                'excerpt' => 'Vì sao chỉ dùng sữa rửa mặt là chưa đủ khi bạn thoa kem chống nắng hàng ngày? Khám phá cách kết hợp Nước tẩy trang Micellar và Gel rửa mặt độ pH 5.5 giúp lỗ chân lông sạch thoáng.',
                'tags' => ['Double Cleansing', 'Nước Tẩy Trang', 'Sữa Rửa Mặt pH 5.5'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 9
            [
                'slug' => 'so-sanh-aha-va-bha-chon-hoat-chat-tay-da-chet-hoa-hoc-nao',
                'title' => 'So Sánh AHA Và BHA: Làn Da Của Bạn Phù Hợp Với Hoạt Chất Tẩy Tế Bào Chết Hóa Học Nào?',
                'category_slug' => 'hoat-chat-duong-da',
                'category' => 'Hoạt Chất Dưỡng Da (BHA, Retinol, B5)',
                'skin_type' => 'Mọi loại da',
                'published_at' => '07/11/2026',
                'published_date' => '2026-11-07',
                'views' => 7540,
                'comments_count' => 112,
                'read_time' => '7 phút đọc',
                'author' => 'Dược sĩ Thanh Tuyền',
                'author_role' => 'Cố vấn Hoạt chất Mỹ phẩm',
                'image' => self::VERIFIED_IMAGES[2],
                'excerpt' => 'AHA tan trong nước giúp làm sáng bề mặt da khô xỉn màu, trong khi BHA tan trong dầu đi sâu giải phóng bã nhờn cho da dầu mụn. Xem ngay bảng so sánh chi tiết để chọn đúng sản phẩm.',
                'tags' => ['AHA vs BHA', 'Tẩy Tế Bào Chết', 'Salicylic Acid'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 10
            [
                'slug' => 'review-kem-duong-nhan-sam-sulwhasoo-concentrated-ginseng',
                'title' => 'Đánh Giá Kem Dưỡng Nhân Sâm Cô Đặc Sulwhasoo: Bí Mật Trẻ Hóa Làn Da Hoàng Cung Hàn Quốc',
                'category_slug' => 'review-my-pham',
                'category' => 'Review Sản Phẩm Mỹ Phẩm',
                'skin_type' => 'Da khô',
                'published_at' => '06/11/2026',
                'published_date' => '2026-11-06',
                'views' => 6820,
                'comments_count' => 88,
                'read_time' => '6 phút đọc',
                'author' => 'Beauty Editor Lan Anh',
                'author_role' => 'Biên tập viên Làm đẹp SHINE',
                'image' => self::VERIFIED_IMAGES[4],
                'excerpt' => 'Trải nghiệm thực tế dòng kem dưỡng Sulwhasoo chứa hoạt chất Ginsenomics™ cô đặc gấp 6000 lần giúp nâng cơ, xóa mờ rãnh nhăn và phục hồi sinh khí cho làn da khô lão hóa.',
                'tags' => ['Sulwhasoo', 'Nhân Sâm Hàn Quốc', 'Trẻ Hóa Da'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 11
            [
                'slug' => 'bi-quyet-danh-phan-nuoc-cushion-cang-bong-chuan-han-quoc',
                'title' => '7 Mẹo Đánh Phấn Nước (Cushion) Căng Bóng, Lâu Trôi Suốt Ngày Dài Không Bị Xuống Tông',
                'category_slug' => 'trang-diem-chong-nang',
                'category' => 'Trang Điểm & Chống Nắng Bảo Vệ Da',
                'skin_type' => 'Mọi loại da',
                'published_at' => '05/11/2026',
                'published_date' => '2026-11-05',
                'views' => 5980,
                'comments_count' => 64,
                'read_time' => '5 phút đọc',
                'author' => 'Chuyên gia Makeup Bảo Ngọc',
                'author_role' => 'Giám đốc Sáng tạo SHINE Studio',
                'image' => self::VERIFIED_IMAGES[7],
                'excerpt' => 'Hướng dẫn cách cấp ẩm trước khi đánh nền, kỹ thuật lấy lượng phấn vừa đủ trên nắp hộp Cushion và cố định lớp makeup giúp da bóng khỏe như sương mà không bóng dầu.',
                'tags' => ['Cushion Hàn Quốc', 'Lớp Nền Căng Bóng', 'Mẹo Trang Điểm'],
                'related_category_slug' => 'trang-diem',
            ],
            // 12
            [
                'slug' => 'hyaluronic-acid-da-phan-tu-la-gi-cach-thoa-ha-khong-hut-am-nguoc',
                'title' => 'Hyaluronic Acid Đa Phân Tử Là Gì? Cách Thoa Serum HA Để Không Bị "Hút Ẩm Ngược"',
                'category_slug' => 'hoat-chat-duong-da',
                'category' => 'Hoạt Chất Dưỡng Da (BHA, Retinol, B5)',
                'skin_type' => 'Da khô',
                'published_at' => '04/11/2026',
                'published_date' => '2026-11-04',
                'views' => 8120,
                'comments_count' => 119,
                'read_time' => '6 phút đọc',
                'author' => 'Dược sĩ Thanh Tuyền',
                'author_role' => 'Cố vấn Hoạt chất Mỹ phẩm',
                'image' => self::VERIFIED_IMAGES[5],
                'excerpt' => 'Giải mã lý do vì sao thoa Serum Hyaluronic Acid trên nền da khô trong phòng máy lạnh lại khiến da căng rát hơn, cùng quy tắc thoa trên nền da ẩm khóa bằng kem dưỡng.',
                'tags' => ['Hyaluronic Acid', 'Cấp Nước Đa Tầng', 'Chống Khô Da'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 13
            [
                'slug' => 'cach-chon-mau-son-moi-lancome-phu-hop-voi-undertone-da-nguoi-viet',
                'title' => 'Cách Chọn Màu Son Lì & Son Dưỡng Lancôme Tôn Da Trắng Sáng Theo Undertone Người Việt',
                'category_slug' => 'trang-diem-chong-nang',
                'category' => 'Trang Điểm & Chống Nắng Bảo Vệ Da',
                'skin_type' => 'Mọi loại da',
                'published_at' => '03/11/2026',
                'published_date' => '2026-11-03',
                'views' => 6210,
                'comments_count' => 73,
                'read_time' => '5 phút đọc',
                'author' => 'Chuyên gia Makeup Bảo Ngọc',
                'author_role' => 'Giám đốc Sáng tạo SHINE Studio',
                'image' => self::VERIFIED_IMAGES[8],
                'excerpt' => 'Bí quyết xác định sắc độ da (Warm, Cool hay Neutral Undertone) trên cổ tay để chọn các tông Hồng Đất, Đỏ Gạch, Cam San Hô giúp gương mặt bừng sáng và trắng răng.',
                'tags' => ['Son Môi Lancôme', 'Chọn Màu Son', 'Trang Điểm Môi'],
                'related_category_slug' => 'trang-diem',
            ],
            // 14
            [
                'slug' => 'cham-soc-doi-moi-kho-nut-tham-sam-voi-mat-na-ngu-moi-nhan-sam',
                'title' => 'Quy Trình Phục Hồi Đôi Môi Khô Nứt, Viền Môi Sậm Màu Với Mặt Nạ Ngủ Môi Nhân Sâm',
                'category_slug' => 'cham-soc-da-khoa-hoc',
                'category' => 'Chăm Sóc Da Khoa Học',
                'skin_type' => 'Da khô',
                'published_at' => '02/11/2026',
                'published_date' => '2026-11-02',
                'views' => 5340,
                'comments_count' => 52,
                'read_time' => '4 phút đọc',
                'author' => 'Beauty Editor Lan Anh',
                'author_role' => 'Biên tập viên Làm đẹp SHINE',
                'image' => self::VERIFIED_IMAGES[8],
                'excerpt' => 'Thói quen liếm môi và quên tẩy trang môi khiến đôi môi bong tróc và lộ rãnh nhăn. Khám phá cách ủ môi mỗi tối với bơ Murumuru và tinh dầu Nhân Sâm giúp môi hồng hào mềm mọng.',
                'tags' => ['Dưỡng Môi', 'Mặt Nạ Ngủ Môi', 'Chăm Sóc Môi'],
                'related_category_slug' => 'trang-diem',
            ],
            // 15
            [
                'slug' => 'review-nuoc-hoa-nu-lancome-la-vie-est-belle-rose-extraordinaire',
                'title' => 'Review Nước Hoa Lancôme La Vie Est Belle Rose Extraordinaire: Bản Giao Hưởng Hoa Hồng Pháp',
                'category_slug' => 'review-my-pham',
                'category' => 'Review Sản Phẩm Mỹ Phẩm',
                'skin_type' => 'Mọi loại da',
                'published_at' => '01/11/2026',
                'published_date' => '2026-11-01',
                'views' => 7790,
                'comments_count' => 108,
                'read_time' => '6 phút đọc',
                'author' => 'Chuyên gia Hương thơm Hoàng Quân',
                'author_role' => 'Chuyên gia Body Care & Fragrance',
                'image' => self::VERIFIED_IMAGES[10],
                'excerpt' => 'Khám phá 3 tầng hương quyến rũ từ cam Bergamot, bộ ba tinh chất hoa hồng Damask quý hiếm đến nền xạ hương Ambroxan sang trọng giúp phái đẹp ghi dấu ấn khó quên.',
                'tags' => ['Nước Hoa Lancôme', 'La Vie Est Belle', 'Review Nước Hoa'],
                'related_category_slug' => 'nuoc-hoa',
            ],
            // 16
            [
                'slug' => 'cach-tri-viem-nang-long-va-mun-lung-voi-muoi-hong-bha-toan-than',
                'title' => 'Giải Pháp Giảm Viêm Nang Lông & Mụn Lưng Hiệu Quả Với Tẩy Tế Bào Chết Muối Hồng BHA',
                'category_slug' => 'cham-soc-da-khoa-hoc',
                'category' => 'Chăm Sóc Da Khoa Học',
                'skin_type' => 'Da dầu',
                'published_at' => '31/10/2026',
                'published_date' => '2026-10-31',
                'views' => 6890,
                'comments_count' => 91,
                'read_time' => '6 phút đọc',
                'author' => 'BS. Da Liễu Minh Châu',
                'author_role' => 'Chuyên gia Da liễu SHINE Beauty',
                'image' => self::VERIFIED_IMAGES[9],
                'excerpt' => 'Kết hợp muối hồng Himalaya giàu khoáng chất cùng 0.5% Salicylic Acid (BHA) và dầu hoa trà Nhật Bản giúp làm thông thoáng lỗ chân lông vùng lưng, ngực và bắp tay chỉ sau 3 tuần.',
                'tags' => ['Viêm Nang Lông', 'Giảm Mụn Lưng', 'Tẩy Da Chết Body'],
                'related_category_slug' => 'cham-soc-co-the',
            ],
            // 17
            [
                'slug' => 'phan-biet-kem-chong-nang-vat-ly-va-hoa-hoc-cho-da-dau-nhay-cam',
                'title' => 'Phân Biệt Kem Chống Nắng Vật Lý, Hóa Học & Lai (Hybrid): Đâu Là Chân Ái Cho Da Dầu Nhạy Cảm?',
                'category_slug' => 'trang-diem-chong-nang',
                'category' => 'Trang Điểm & Chống Nắng Bảo Vệ Da',
                'skin_type' => 'Da dầu',
                'published_at' => '30/10/2026',
                'published_date' => '2026-10-30',
                'views' => 8310,
                'comments_count' => 124,
                'read_time' => '7 phút đọc',
                'author' => 'Dược sĩ Thanh Tuyền',
                'author_role' => 'Cố vấn Hoạt chất Mỹ phẩm',
                'image' => self::VERIFIED_IMAGES[3],
                'excerpt' => 'Tìm hiểu ưu nhược điểm của Zinc Oxide, Titanium Dioxide và các màng lọc chống nắng thế hệ mới công nghệ SuperVeil-UV 360™ giúp bảo vệ da tối đa mà không gây cay mắt hay bít tắc.',
                'tags' => ['Kem Chống Nắng SPF50+', 'Da Dầu Nhạy Cảm', 'Tia UVA/UVB'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 18
            [
                'slug' => 'cong-dung-cua-zinc-pca-va-niacinamide-trong-kiem-soat-dau-nhon',
                'title' => 'Giải Mã Cặp Đôi Hoạt Chất Zinc PCA 1% & Niacinamide 10% Trong Kiểm Soát Nhờn Và Giảm Thâm Mụn',
                'category_slug' => 'hoat-chat-duong-da',
                'category' => 'Hoạt Chất Dưỡng Da (BHA, Retinol, B5)',
                'skin_type' => 'Da dầu',
                'published_at' => '29/10/2026',
                'published_date' => '2026-10-29',
                'views' => 6120,
                'comments_count' => 69,
                'read_time' => '5 phút đọc',
                'author' => 'Dược sĩ Thanh Tuyền',
                'author_role' => 'Cố vấn Hoạt chất Mỹ phẩm',
                'image' => self::VERIFIED_IMAGES[1],
                'excerpt' => 'Kẽm sinh học (Zinc PCA) ức chế enzyme 5-alpha reductase gây tiết bã nhờn quá mức, khi đi cùng Niacinamide sẽ nhân đôi khả năng kháng viêm và làm sáng vết thâm đỏ sau mụn.',
                'tags' => ['Zinc PCA', 'Niacinamide', 'Kiềm Dầu'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 19
            [
                'slug' => 'review-xit-khoang-la-roche-posay-thermal-spring-water-chinh-hang',
                'title' => 'Review Xịt Khoáng La Roche-Posay Giàu Selenium: 5 Thời Điểm Xịt Khoáng Giúp Da Dịu Mát Tức Thì',
                'category_slug' => 'review-my-pham',
                'category' => 'Review Sản Phẩm Mỹ Phẩm',
                'skin_type' => 'Da nhạy cảm',
                'published_at' => '28/10/2026',
                'published_date' => '2026-10-28',
                'views' => 5490,
                'comments_count' => 48,
                'read_time' => '4 phút đọc',
                'author' => 'Beauty Editor Lan Anh',
                'author_role' => 'Biên tập viên Làm đẹp SHINE',
                'image' => self::VERIFIED_IMAGES[5],
                'excerpt' => 'Không chỉ đơn thuần là nước tinh khiết, nguồn nước khoáng nhiệt từ Pháp chứa hàm lượng Selenium tự nhiên cao giúp chống oxy hóa, làm dịu da sau đi nắng và giữ lớp nền trang điểm mịn màng.',
                'tags' => ['Xịt Khoáng Pháp', 'Selenium', 'Làm Dịu Da'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 20
            [
                'slug' => 'huong-dan-ke-chan-may-tu-nhien-phu-hop-voi-5-dang-khuon-mat',
                'title' => 'Hướng Dẫn Chọn Dáng Chân Mày & Kỹ Thuật Phẩy Sợi Tự Nhiên Cho 5 Dáng Khuôn Mặt Á Đông',
                'category_slug' => 'trang-diem-chong-nang',
                'category' => 'Trang Điểm & Chống Nắng Bảo Vệ Da',
                'skin_type' => 'Mọi loại da',
                'published_at' => '27/10/2026',
                'published_date' => '2026-10-27',
                'views' => 4980,
                'comments_count' => 43,
                'read_time' => '5 phút đọc',
                'author' => 'Chuyên gia Makeup Bảo Ngọc',
                'author_role' => 'Giám đốc Sáng tạo SHINE Studio',
                'image' => self::VERIFIED_IMAGES[7],
                'excerpt' => 'Mặt tròn, mặt trái xoan, mặt vuông hay mặt dài nên kẻ dáng mày ngang Hàn Quốc hay mày cong nhẹ thanh thoát? Xem ngay công thức 3 điểm vàng với chì kẻ mày 3 đầu Shiseido.',
                'tags' => ['Kẻ Chân Mày', 'Shiseido Brow InkTrio', 'Makeup Cơ Bản'],
                'related_category_slug' => 'trang-diem',
            ],
            // 21
            [
                'slug' => 'ceramide-la-gi-vai-tro-cua-ceramide-trong-hang-rao-bao-ve-da',
                'title' => 'Ceramide Là Gì? Vì Sao Ceramide Được Gọi Là "Xi Măng" Giữ Vững Hàng Rào Bảo Vệ Da?',
                'category_slug' => 'hoat-chat-duong-da',
                'category' => 'Hoạt Chất Dưỡng Da (BHA, Retinol, B5)',
                'skin_type' => 'Da khô',
                'published_at' => '26/10/2026',
                'published_date' => '2026-10-26',
                'views' => 6740,
                'comments_count' => 82,
                'read_time' => '6 phút đọc',
                'author' => 'BS. Da Liễu Minh Châu',
                'author_role' => 'Chuyên gia Da liễu SHINE Beauty',
                'image' => self::VERIFIED_IMAGES[4],
                'excerpt' => 'Chiếm tới 50% cấu trúc lipid ở lớp sừng, Ceramide liên kết các tế bào da để ngăn chặn mất nước và vi khuẩn xâm nhập. Bổ sung Ceramide NP, AP, EOP là chìa khóa cho làn da khỏe.',
                'tags' => ['Ceramide', 'Hàng Rào Bảo Vệ Da', 'Khóa Ẩm'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 22
            [
                'slug' => 'review-kem-nen-shiseido-synchro-skin-radiant-lifting-foundation',
                'title' => 'Review Kem Nền Shiseido Synchro Skin Radiant Lifting: Lớp Nền Bắt Sáng Không Đọng Rãnh Nhăn',
                'category_slug' => 'review-my-pham',
                'category' => 'Review Sản Phẩm Mỹ Phẩm',
                'skin_type' => 'Da khô',
                'published_at' => '25/10/2026',
                'published_date' => '2026-10-25',
                'views' => 5830,
                'comments_count' => 61,
                'read_time' => '5 phút đọc',
                'author' => 'Chuyên gia Makeup Bảo Ngọc',
                'author_role' => 'Giám đốc Sáng tạo SHINE Studio',
                'image' => self::VERIFIED_IMAGES[7],
                'excerpt' => 'Kiểm chứng công nghệ Light-Adjusting tự động thích ứng với mọi điều kiện ánh sáng của kem nền Shiseido giúp che phủ thâm nám hoàn hảo mà vẫn ẩm mượt suốt 24 giờ.',
                'tags' => ['Kem Nền Shiseido', 'Synchro Skin', 'Review Makeup'],
                'related_category_slug' => 'trang-diem',
            ],
            // 23
            [
                'slug' => 'cham-soc-da-tay-mem-min-chong-lao-hoa-voi-bo-hat-mo-va-niacinamide',
                'title' => 'Bí Quyết Chăm Sóc Đôi Bàn Tay Mềm Mịn, Trẻ Trung Với Kem Dưỡng Bơ Hạt Mỡ & Niacinamide',
                'category_slug' => 'cham-soc-da-khoa-hoc',
                'category' => 'Chăm Sóc Da Khoa Học',
                'skin_type' => 'Da nhạy cảm',
                'published_at' => '24/10/2026',
                'published_date' => '2026-10-24',
                'views' => 4620,
                'comments_count' => 39,
                'read_time' => '4 phút đọc',
                'author' => 'Beauty Editor Lan Anh',
                'author_role' => 'Biên tập viên Làm đẹp SHINE',
                'image' => self::VERIFIED_IMAGES[2],
                'excerpt' => 'Đôi bàn tay thường xuyên tiếp xúc với nước rửa tay và tia UV nên lão hóa nhanh hơn cả da mặt. Hãy bỏ túi tuýp kem dưỡng tay chứa 30% Glycerin và 4% Niacinamide để bảo vệ da tay mỗi ngày.',
                'tags' => ['Kem Dưỡng Da Tay', 'Bơ Hạt Mỡ', 'Chăm Sóc Cơ Thể'],
                'related_category_slug' => 'cham-soc-co-the',
            ],
            // 24
            [
                'slug' => 'su-khac-biet-giua-nuoc-hoa-edp-edt-va-body-mist-xit-thom-toan-than',
                'title' => 'Phân Biệt Nước Hoa EDP, EDT Và Xịt Thơm Toàn Thân (Hair & Body Mist): Dùng Sao Cho Tinh Tế?',
                'category_slug' => 'review-my-pham',
                'category' => 'Review Sản Phẩm Mỹ Phẩm',
                'skin_type' => 'Mọi loại da',
                'published_at' => '23/10/2026',
                'published_date' => '2026-10-23',
                'views' => 6510,
                'comments_count' => 78,
                'read_time' => '5 phút đọc',
                'author' => 'Chuyên gia Hương thơm Hoàng Quân',
                'author_role' => 'Chuyên gia Body Care & Fragrance',
                'image' => self::VERIFIED_IMAGES[11],
                'excerpt' => 'Khi nào nên chọn Eau de Parfum nồng nàn quyến rũ cho buổi tiệc tối, và khi nào Xịt thơm tóc & cơ thể Lancôme Idôle thanh mát lại là lựa chọn hoàn hảo cho môi trường công sở?',
                'tags' => ['Body Mist', 'Eau de Parfum', 'Hương Thơm Tinh Tế'],
                'related_category_slug' => 'nuoc-hoa',
            ],
            // 25
            [
                'slug' => 'cach-xu-ly-mun-viem-sung-do-cap-toc-trong-24-gio-khong-de-lai-seo',
                'title' => 'Cách Xử Lý Nốt Mụn Viêm Sưng Đỏ Cấp Tốc Trong 24 Giờ Không Để Lại Thâm Rỗ',
                'category_slug' => 'cham-soc-da-khoa-hoc',
                'category' => 'Chăm Sóc Da Khoa Học',
                'skin_type' => 'Da dầu',
                'published_at' => '22/10/2026',
                'published_date' => '2026-10-22',
                'views' => 8790,
                'comments_count' => 146,
                'read_time' => '6 phút đọc',
                'author' => 'BS. Da Liễu Minh Châu',
                'author_role' => 'Chuyên gia Da liễu SHINE Beauty',
                'image' => self::VERIFIED_IMAGES[1],
                'excerpt' => 'Tuyệt đối không tự ý cạy nặn khi mụn chưa gom cồi! Áp dụng ngay phác đồ chấm mụn chứa LHA, Salicylic Acid và Phylobioma giúp giảm sưng viêm chỉ sau một đêm.',
                'tags' => ['Chấm Mụn Cấp Tốc', 'Effaclar Duo+M', 'Ngừa Thâm Mụn'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 26
            [
                'slug' => 'panthenol-vitamin-b5-va-madecassoside-rau-ma-cap-doi-phuc-hoi-da',
                'title' => 'Panthenol (Vitamin B5) & Madecassoside (Rau Má): Cặp Đôi Vàng Phục Hồi Da Sau Treatment',
                'category_slug' => 'hoat-chat-duong-da',
                'category' => 'Hoạt Chất Dưỡng Da (BHA, Retinol, B5)',
                'skin_type' => 'Da nhạy cảm',
                'published_at' => '21/10/2026',
                'published_date' => '2026-10-21',
                'views' => 7260,
                'comments_count' => 97,
                'read_time' => '6 phút đọc',
                'author' => 'Dược sĩ Thanh Tuyền',
                'author_role' => 'Cố vấn Hoạt chất Mỹ phẩm',
                'image' => self::VERIFIED_IMAGES[0],
                'excerpt' => 'Khám phá cơ chế thúc đẩy nguyên bào sợi sản sinh mô mới của Vitamin B5 kết hợp khả năng làm dịu viêm đỏ của chiết xuất Rau má tinh khiết trên nền da nhạy cảm.',
                'tags' => ['Panthenol B5', 'Madecassoside', 'Rau Má Phục Hồi'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 27
            [
                'slug' => 'tai-sao-phai-thoa-kem-chong-nang-ngay-ca-khi-lam-viec-trong-van-phong',
                'title' => 'Vì Sao Bạn Vẫn Phải Thoa Kem Chống Nắng Đủ Lượng Ngay Cả Khi Ngồi Trong Văn Phòng?',
                'category_slug' => 'trang-diem-chong-nang',
                'category' => 'Trang Điểm & Chống Nắng Bảo Vệ Da',
                'skin_type' => 'Mọi loại da',
                'published_at' => '20/10/2026',
                'published_date' => '2026-10-20',
                'views' => 5670,
                'comments_count' => 67,
                'read_time' => '5 phút đọc',
                'author' => 'BS. Da Liễu Minh Châu',
                'author_role' => 'Chuyên gia Da liễu SHINE Beauty',
                'image' => self::VERIFIED_IMAGES[6],
                'excerpt' => 'Tia UVA bước sóng dài có thể xuyên qua cửa kính văn phòng và đám mây dày để phá hủy sợi Elastin gây sạm nám. Hãy duy trì thói quen chống nắng mỗi sáng để giữ làn da tươi trẻ.',
                'tags' => ['Chống Nắng Văn Phòng', 'Tia UVA', 'Ngừa Sạm Nám'],
                'related_category_slug' => 'cham-soc-da',
            ],
            // 28
            [
                'slug' => 'review-sua-tam-tinh-dau-hoa-tra-nhat-ban-shiseido-kuyura-duong-am',
                'title' => 'Review Sữa Tắm Tinh Dầu Hoa Trà Nhật Bản Shiseido Kuyura: Làn Da Mềm Mượt Như Lụa Tơ Tằm',
                'category_slug' => 'review-my-pham',
                'category' => 'Review Sản Phẩm Mỹ Phẩm',
                'skin_type' => 'Mọi loại da',
                'published_at' => '19/10/2026',
                'published_date' => '2026-10-19',
                'views' => 5180,
                'comments_count' => 55,
                'read_time' => '5 phút đọc',
                'author' => 'Chuyên gia Hương thơm Hoàng Quân',
                'author_role' => 'Chuyên gia Body Care & Fragrance',
                'image' => self::VERIFIED_IMAGES[9],
                'excerpt' => 'Đánh giá lớp bọt tơ tằm siêu mịn kết hợp tinh dầu hoa trà Tsubaki và Hyaluronic Acid giúp làm sạch cơ thể dịu nhẹ mà không gây khô căng sau khi tắm.',
                'tags' => ['Sữa Tắm Shiseido', 'Tinh Dầu Hoa Trà', 'Dưỡng Ẩm Body'],
                'related_category_slug' => 'cham-soc-co-the',
            ],
        ];

        return array_map(function (array $post, int $idx) {
            $post['id'] = $idx + 1;
            $post['image'] = self::sanitizeBlogImage($post['image'] ?? null);
            $post['is_hot'] = ($post['comments_count'] ?? 0) >= 110;

            if (empty($post['key_takeaways'])) {
                $post['key_takeaways'] = [
                    'Lựa chọn sản phẩm chính hãng phù hợp đúng với tình trạng da (' . $post['skin_type'] . ') và nhu cầu thực tế.',
                    'Tuân thủ quy tắc làm sạch dịu nhẹ, cấp ẩm đủ tầng và bảo vệ da bằng kem chống nắng phổ rộng SPF50+ PA++++ mỗi ngày.',
                    'Kết hợp hoạt chất theo nồng độ từ thấp đến cao và luôn lắng nghe phản hồi của hàng rào bảo vệ da.',
                ];
            }

            if (empty($post['sections'])) {
                $post['sections'] = [
                    [
                        'heading' => '1. Phân tích chuyên sâu từ góc nhìn khoa học da liễu',
                        'content' => $post['excerpt'] . ' Khi hiểu rõ cơ chế hoạt động của từng hoạt chất trên bề mặt biểu bì, bạn sẽ xây dựng được chu trình chăm sóc da tinh gọn nhưng mang lại hiệu quả cải thiện rõ rệt chỉ sau 2–4 tuần kiên trì.',
                    ],
                    [
                        'heading' => '2. Hướng dẫn cách ứng dụng vào chu trình dưỡng da hàng ngày',
                        'content' => 'Hãy bắt đầu bằng bước làm sạch chuẩn pH 5.5 để giữ nguyên lớp màng ẩm tự nhiên. Sau đó thoa tinh chất đặc trị trên nền da vừa đủ ẩm, vỗ nhẹ bằng đầu ngón tay để dưỡng chất thẩm thấu hoàn toàn trước khi khóa lại bằng kem dưỡng phục hồi chuyên sâu.',
                    ],
                    [
                        'heading' => '3. Lời khuyên từ chuyên gia SHINE Beauty',
                        'content' => 'Một làn da đẹp bền vững là làn da có hàng rào hydrolipid khỏe mạnh. Đừng quên uống đủ nước, ngủ trước 23h và dặm lại kem chống nắng định kỳ để bảo vệ thành quả dưỡng da của bạn mỗi ngày.',
                    ],
                ];
            }

            return $post;
        }, $rawPosts, array_keys($rawPosts));
    }

    /**
     * Hiển thị trang danh sách bài viết Blog Mỹ Phẩm & Chăm Sóc Da (/blog).
     */
    public function index(Request $request): View
    {
        $topics = $this->getTopics();
        $selectedTopic = trim((string) $request->query('topic', ''));
        $selectedSkin = trim((string) $request->query('skin_type', ''));
        $search = trim((string) $request->query('search', ''));
        $sort = trim((string) $request->query('sort', 'newest'));
        if (!in_array($sort, ['newest', 'most_viewed', 'most_commented'], true)) {
            $sort = 'newest';
        }

        $allPosts = collect(self::getAllPosts());

        $filteredPosts = $allPosts->filter(function (array $post) use ($selectedTopic, $selectedSkin, $search) {
            if ($selectedTopic !== '' && $post['category_slug'] !== $selectedTopic) {
                return false;
            }
            if ($selectedSkin !== '' && $post['skin_type'] !== $selectedSkin && $post['skin_type'] !== 'Mọi loại da') {
                return false;
            }
            if ($search !== '') {
                $haystack = mb_strtolower($post['title'] . ' ' . $post['excerpt'] . ' ' . implode(' ', $post['tags']));
                if (!str_contains($haystack, mb_strtolower($search))) {
                    return false;
                }
            }
            return true;
        });

        // Sắp xếp bài viết theo bộ lọc: Ngày mới nhất | Xem nhiều nhất | Bài viết Hot (Bình luận nhiều nhất)
        if ($sort === 'most_viewed') {
            $filteredPosts = $filteredPosts->sortByDesc('views')->values();
        } elseif ($sort === 'most_commented') {
            $filteredPosts = $filteredPosts->sortByDesc('comments_count')->values();
        } else {
            $filteredPosts = $filteredPosts->sortByDesc('published_date')->values();
        }

        // Phân trang: tối đa 3 hàng x 4 cột = 12 bài viết mỗi trang
        $perPage = 12;
        $currentPage = max(1, (int) LengthAwarePaginator::resolveCurrentPage());
        $paginatedPosts = new LengthAwarePaginator(
            $filteredPosts->forPage($currentPage, $perPage)->values(),
            $filteredPosts->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $recommendedProducts = \Illuminate\Support\Facades\Cache::remember('blog.recommended_products.v1', 300, fn () => Product::visible()
            ->with(['brand', 'category'])
            ->latest('id')
            ->take(4)
            ->get());

        $categories = \Illuminate\Support\Facades\Cache::remember('home.categories.v1', 300, fn () => Category::withCount('products')->get());

        return view('blog.index', [
            'topics' => $topics,
            'posts' => $paginatedPosts,
            'allPostsCount' => $allPosts->count(),
            'selectedTopic' => $selectedTopic,
            'selectedSkin' => $selectedSkin,
            'search' => $search,
            'sort' => $sort,
            'recommendedProducts' => $recommendedProducts,
            'categories' => $categories,
        ]);
    }

    /**
     * Hiển thị chi tiết một bài viết Blog Mỹ Phẩm & Chăm Sóc Da (/blog/{slug}).
     */
    public function show(string $slug): View
    {
        $allPosts = collect(self::getAllPosts());
        $post = $allPosts->firstWhere('slug', $slug);

        if (!$post) {
            abort(404);
        }

        $relatedPosts = $allPosts
            ->where('slug', '!=', $slug)
            ->where('category_slug', $post['category_slug'])
            ->take(3)
            ->values();

        if ($relatedPosts->count() < 3) {
            $relatedPosts = $allPosts->where('slug', '!=', $slug)->take(3)->values();
        }

        $relatedProductsQuery = Product::visible()->with(['brand', 'category']);
        if (!empty($post['related_category_slug'])) {
            $relatedProductsQuery->whereHas('category', function ($q) use ($post) {
                $q->where('slug', $post['related_category_slug']);
            });
        }
        $relatedProducts = $relatedProductsQuery->take(4)->get();
        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::visible()->with(['brand', 'category'])->take(4)->get();
        }

        return view('blog.show', [
            'post' => $post,
            'topics' => $this->getTopics(),
            'relatedPosts' => $relatedPosts,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
