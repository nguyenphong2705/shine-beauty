<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'price',
        'sale_price',
        'stock',
        'images',
        'description',
        'ingredients',
        'skin_type',
        'volume',
        'is_best_seller',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_best_seller' => 'boolean',
            'price' => 'integer',
            'sale_price' => 'integer',
            'stock' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getEffectivePriceAttribute(): int
    {
        return $this->sale_price ?? $this->price;
    }

    public function getFinalPriceAttribute(): int
    {
        return $this->sale_price ?? $this->price;
    }

    public function getProductCodeAttribute(): string
    {
        return $this->sku ?: ('LMN-SP' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT));
    }

    public const FALLBACK_IMAGE = 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=80';

    public static function sanitizeImageUrl(?string $url): string
    {
        if (!$url || str_contains($url, '1608248597359-0e6d526a6c18')) {
            return self::FALLBACK_IMAGE;
        }

        return $url;
    }

    public function getImagesAttribute($value): array
    {
        $decoded = is_string($value) ? (json_decode($value, true) ?: []) : (is_array($value) ? $value : []);
        if (empty($decoded)) {
            return [self::FALLBACK_IMAGE];
        }

        return array_values(array_map([self::class, 'sanitizeImageUrl'], $decoded));
    }

    public function getFirstImageAttribute(): string
    {
        $imgs = $this->images;
        if (is_array($imgs) && count($imgs) > 0) {
            return self::sanitizeImageUrl($imgs[0]);
        }
        return self::FALLBACK_IMAGE;
    }

    public static function ensureUnder500kProductsSeeded(): void
    {
        if (self::query()->where('sku', 'LMN-SP044')->exists()) {
            return;
        }

        $categories = Category::query()->pluck('id', 'slug');
        $brands = Brand::query()->pluck('id', 'name');
        if ($categories->isEmpty() || $brands->isEmpty()) {
            return;
        }

        $catSkincare = $categories['cham-soc-da'] ?? $categories->first();
        $catMakeup = $categories['trang-diem'] ?? $categories->first();
        $catBodycare = $categories['cham-soc-co-the'] ?? $categories->first();
        $catPerfume = $categories['nuoc-hoa'] ?? $categories->first();

        $brandLaRoche = $brands['La Roche-Posay'] ?? $brands->first();
        $brandSulwhasoo = $brands['Sulwhasoo'] ?? $brands->first();
        $brandShiseido = $brands['Shiseido'] ?? $brands->first();
        $brandLancome = $brands['Lancôme'] ?? $brands->first();

        $newProducts = [
            [
                'sku' => 'LMN-SP013',
                'category_id' => $catSkincare,
                'brand_id' => $brandLaRoche,
                'name' => 'Sữa Rửa Mặt Tạo Bọt Dịu Nhẹ La Roche-Posay Effaclar Purifying Gel',
                'slug' => 'sua-rua-mat-tao-bot-diu-nhe-la-roche-posay-effaclar-gel',
                'price' => 385000,
                'sale_price' => 315000,
                'stock' => 50,
                'images' => [
                    'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Gel rửa mặt tạo bọt không chứa xà phòng với nước khoáng La Roche-Posay và Zinc Pidolate giúp làm sạch sâu bụi bẩn, bã nhờn dư thừa mà vẫn giữ độ pH 5.5 dịu nhẹ cho làn da dầu mụn.',
                'ingredients' => 'Aqua, Sodium Laureth Sulfate, PEG-8, Coco-Betaine, Hexylene Glycol, Sodium Chloride, Zinc PCA, Sodium Hydroxide, Citric Acid.',
                'skin_type' => 'Da dầu',
                'volume' => '200ml',
                'is_best_seller' => true,
            ],
            [
                'sku' => 'LMN-SP014',
                'category_id' => $catSkincare,
                'brand_id' => $brandLaRoche,
                'name' => 'Nước Tẩy Trang Làm Sạch Sâu La Roche-Posay Micellar Water Sensitive',
                'slug' => 'nuoc-tay-trang-lam-sach-sau-la-roche-posay-micellar-water',
                'price' => 430000,
                'sale_price' => 355000,
                'stock' => 60,
                'images' => [
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1617897903246-719242758050?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Công nghệ Glyco-Micellar cuốn sạch 99% lớp trang điểm, kem chống nắng và bụi mịn PM2.5 chỉ sau một lượt bông tẩy trang, bổ sung Glycerin cấp ẩm tức thì cho da nhạy cảm.',
                'ingredients' => 'Aqua / Water, PEG-7 Caprylic/Capric Glycerides, Poloxamer 124, Poloxamer 184, PEG-6 Caprylic/Capric Glycerides, Glycerin, Polysorbate 80, Disodium EDTA.',
                'skin_type' => 'Da nhạy cảm',
                'volume' => '400ml',
                'is_best_seller' => true,
            ],
            [
                'sku' => 'LMN-SP015',
                'category_id' => $catSkincare,
                'brand_id' => $brandSulwhasoo,
                'name' => 'Nước Hoa Hồng Cân Bằng Ẩm Sulwhasoo Essential Comfort Balancing Water',
                'slug' => 'nuoc-hoa-hong-can-bang-am-sulwhasoo-essential-comfort',
                'price' => 480000,
                'sale_price' => 395000,
                'stock' => 45,
                'images' => [
                    'https://images.unsplash.com/photo-1612817288484-6f916006741a?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Nước cân bằng kết cấu dạng gel mỏng nhẹ chiết xuất từ hợp chất thảo mộc JAUM Moisturizing Complex™ giúp làm dịu da khô ráp và cấp nước tức thì sau bước rửa mặt.',
                'ingredients' => 'Water, Alcohol Denat., Betaine, Butylene Glycol, Glyceryl Polymethacrylate, Camellia Sinensis Leaf Extract, Liriope Platyphylla Root Extract, Panthenol.',
                'skin_type' => 'Da khô',
                'volume' => '75ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP016',
                'category_id' => $catSkincare,
                'brand_id' => $brandLaRoche,
                'name' => 'Xịt Khoáng Làm Dịu & Cấp Nước Tức Thì La Roche-Posay Thermal Water',
                'slug' => 'xit-khoang-lam-diu-cap-nuoc-la-roche-posay-thermal-water',
                'price' => 295000,
                'sale_price' => 235000,
                'stock' => 80,
                'images' => [
                    'https://images.unsplash.com/photo-1617897903246-719242758050?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Nguồn khoáng thiên nhiên giàu Selenium và nguyên tố vi lượng từ Pháp giúp làm dịu vết ửng đỏ, giảm kích ứng do nắng nóng và khóa ẩm nhẹ nhàng suốt cả ngày.',
                'ingredients' => 'Aqua / Thermal Spring Water (100% Pure La Roche-Posay Thermal Spring Water Rich in Selenium).',
                'skin_type' => 'Mọi loại da',
                'volume' => '150ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP017',
                'category_id' => $catSkincare,
                'brand_id' => $brandShiseido,
                'name' => 'Kem Chống Nắng Kiềm Dầu Nâng Tông Shiseido Urban Environment SPF50+',
                'slug' => 'kem-chong-nang-kiem-dau-nang-tong-shiseido-urban-spf50',
                'price' => 490000,
                'sale_price' => 425000,
                'stock' => 55,
                'images' => [
                    'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Sữa chống nắng vật lý lai hóa học ứng dụng công nghệ SuperVeil-UV 360™ bảo vệ da toàn diện trước tia UVA/UVB, kiềm dầu suốt 8 giờ và nâng tông hồng hào tự nhiên.',
                'ingredients' => 'Water, Zinc Oxide, Ethylhexyl Methoxycinnamate, Cyclopentasiloxane, Glycerin, Sodium Hyaluronate, Scutellaria Baicalensis Root Extract.',
                'skin_type' => 'Da dầu',
                'volume' => '30ml',
                'is_best_seller' => true,
            ],
            [
                'sku' => 'LMN-SP018',
                'category_id' => $catSkincare,
                'brand_id' => $brandLaRoche,
                'name' => 'Gel Chấm Mụn Giảm Sưng Nhanh BHA & Niacinamide Effaclar Duo+M Mini',
                'slug' => 'gel-cham-mun-giam-sung-bha-niacinamide-effaclar-duo-mini',
                'price' => 340000,
                'sale_price' => 275000,
                'stock' => 70,
                'images' => [
                    'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Gel chấm mụn chuyên biệt với hoạt chất Phylobioma kết hợp Niacinamide, LHA và Salicylic Acid giúp gom cồi mụn nhanh trong 8 giờ và ngăn ngừa vết thâm sau mụn.',
                'ingredients' => 'Aqua, Glycerin, Dimethicone, Isocetyl Stearate, Niacinamide, Salicylic Acid, Capryloyl Salicylic Acid, Zinc PCA, Piroctone Olamine.',
                'skin_type' => 'Da dầu',
                'volume' => '15ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP019',
                'category_id' => $catMakeup,
                'brand_id' => $brandLancome,
                'name' => 'Son Dưỡng Môi Có Màu Căng Mọng Lancôme L\'Absolu Mademoiselle Balm',
                'slug' => 'son-duong-moi-co-mau-cang-mong-lancome-mademoiselle-balm',
                'price' => 460000,
                'sale_price' => 380000,
                'stock' => 40,
                'images' => [
                    'https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Son dưỡng có màu giàu tinh dầu hoa hồng Pháp và tinh chất bạc hà mát lạnh giúp nuôi dưỡng đôi môi mềm mọng, ửng hồng tươi tắn suốt cả ngày dài.',
                'ingredients' => 'Diisostearyl Malate, Hydrogenated Polyisobutene, Phenyl Trimethicone, Rosa Canina Fruit Oil, Macadamia Integrifolia Seed Oil, Tocopherol.',
                'skin_type' => 'Mọi loại da',
                'volume' => '3.2g',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP020',
                'category_id' => $catMakeup,
                'brand_id' => $brandSulwhasoo,
                'name' => 'Mặt Nạ Ngủ Môi Nhân Sâm Mềm Mượt Sulwhasoo Essential Lip Mask',
                'slug' => 'mat-na-ngu-moi-nhan-sam-mem-muot-sulwhasoo-lip-mask',
                'price' => 390000,
                'sale_price' => 320000,
                'stock' => 50,
                'images' => [
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Mặt nạ ủ môi ban đêm giàu chiết xuất Nhân Sâm Đỏ và bơ Murumuru giúp làm mềm lớp tế bào chết khô nứt, trả lại bờ môi căng mọng và hồng hào mỗi sáng thức dậy.',
                'ingredients' => 'Hydrogenated Polyisobutene, Phytosteryl/Isostearyl/Cetyl/Stearyl/Behenyl Dimer Dilinoleate, Astrocaryum Murumuru Seed Butter, Panax Ginseng Seed Oil.',
                'skin_type' => 'Da khô',
                'volume' => '10g',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP021',
                'category_id' => $catMakeup,
                'brand_id' => $brandShiseido,
                'name' => 'Chì Kẻ Mày Tự Nhiên Lâu Trôi Shiseido Brow InkTrio Chống Nước',
                'slug' => 'chi-ke-may-tu-nhien-lau-troi-shiseido-brow-inktrio',
                'price' => 350000,
                'sale_price' => 285000,
                'stock' => 65,
                'images' => [
                    'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Chì kẻ chân mày 3 trong 1 tích hợp đầu chì mảnh định hình sợi, đầu phấn tán tự nhiên và cọ chải định hình lông mày sắc nét, chống trôi mồ hôi suốt 24 giờ.',
                'ingredients' => 'Behenic Acid, Rhus Succedanea Fruit Wax, Triethylhexanoin, Sucrose Tetrastearate Triacetate, Tocopherol, Iron Oxides.',
                'skin_type' => 'Mọi loại da',
                'volume' => '0.3g',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP022',
                'category_id' => $catBodycare,
                'brand_id' => $brandLaRoche,
                'name' => 'Kem Dưỡng Da Tay & Móng Bơ Hạt Mỡ La Roche-Posay Cicaplast Mains',
                'slug' => 'kem-duong-da-tay-bo-hat-mo-la-roche-posay-cicaplast-mains',
                'price' => 260000,
                'sale_price' => 195000,
                'stock' => 90,
                'images' => [
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Kem dưỡng phục hồi da tay khô nứt với 4% Niacinamide và 30% Glycerin tạo màng chắn vô hình bảo vệ đôi bàn tay mềm mịn, thấm nhanh không nhờn rít.',
                'ingredients' => 'Aqua / Water, Glycerin (30%), Butylene Glycol, Cetearyl Alcohol, Niacinamide (4%), Glyceryl Stearate, Butyrospermum Parkii Butter / Shea Butter.',
                'skin_type' => 'Da nhạy cảm',
                'volume' => '50ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP023',
                'category_id' => $catBodycare,
                'brand_id' => $brandShiseido,
                'name' => 'Sữa Tắm Tinh Dầu Hoa Trà Dưỡng Ẩm Shiseido Kuyura Body Care Wash',
                'slug' => 'sua-tam-tinh-dau-hoa-tra-duong-am-shiseido-kuyura',
                'price' => 320000,
                'sale_price' => 255000,
                'stock' => 75,
                'images' => [
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Sữa tắm tạo bọt tơ tằm giàu tinh dầu Hoa Trà Tsubaki Nhật Bản giúp làm sạch dịu nhẹ, lưu lại hương hoa thanh khiết và làn da cơ thể ẩm mịn suốt ngày dài.',
                'ingredients' => 'Water, Lauric Acid, Myristic Acid, Potassium Hydroxide, Glycol Distearate, Camellia Japonica Seed Oil, Sodium Hyaluronate, Glycerin.',
                'skin_type' => 'Mọi loại da',
                'volume' => '350ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP024',
                'category_id' => $catPerfume,
                'brand_id' => $brandLancome,
                'name' => 'Xịt Thơm Toàn Thân Hương Hoa Hồng Lancôme Idôle Hair & Body Mist',
                'slug' => 'xit-thom-toan-than-huong-hoa-hong-lancome-idole-body-mist',
                'price' => 495000,
                'sale_price' => 445000,
                'stock' => 60,
                'images' => [
                    'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Xịt thơm dưỡng ẩm cho tóc và cơ thể mang nốt hương hoa hồng Isparta thanh tao kết hợp hoa nhài trắng và xạ hương sạch sẽ, tươi mát suốt 6 giờ.',
                'ingredients' => 'Alcohol, Aqua / Water, Parfum / Fragrance, Panthenol, Rosa Damascena Flower Water, Jasminum Grandiflorum Flower Extract, Linalool, Limonene.',
                'skin_type' => 'Mọi loại da',
                'volume' => '50ml',
                'is_best_seller' => true,
            ],
            // === BỔ SUNG THÊM 20 SẢN PHẨM ĐA DẠNG CHO 4 DANH MỤC (LMN-SP025 -> LMN-SP044) ===
            [
                'sku' => 'LMN-SP025',
                'category_id' => $catSkincare,
                'brand_id' => $brandLancome,
                'name' => 'Tinh Chất Trẻ Hóa Vùng Mắt Lancôme Advanced Génifique Yeux Light-Pearl',
                'slug' => 'tinh-chat-tre-hoa-vung-mat-lancome-advanced-genifique-yeux-light-pearl',
                'price' => 1650000,
                'sale_price' => 1390000,
                'stock' => 35,
                'images' => [
                    'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Tinh chất dưỡng mắt và mi tích hợp đầu bi lăn thép lạnh xoay 360° giúp giảm bọng mắt, làm mờ quầng thâm và nâng cơ vùng da quanh mắt tươi trẻ rạng rỡ.',
                'ingredients' => 'Aqua, Bifida Ferment Lysate, Propanediol, Glycerin, Caffeine, Arginine, Madecassoside, Adenosine, Chlorella Vulgaris Extract.',
                'skin_type' => 'Da khô',
                'volume' => '20ml',
                'is_best_seller' => true,
            ],
            [
                'sku' => 'LMN-SP026',
                'category_id' => $catSkincare,
                'brand_id' => $brandLaRoche,
                'name' => 'Mặt Nạ Đất Sét Thanh Lọc Lỗ Chân Lông La Roche-Posay Effaclar Clay Mask',
                'slug' => 'mat-na-dat-set-thanh-loc-lo-chan-long-la-roche-posay-effaclar-mask',
                'price' => 465000,
                'sale_price' => 395000,
                'stock' => 48,
                'images' => [
                    'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Mặt nạ đất sét Kaolin & Montmorillonite kết hợp 5% Panthenol B5 giúp hút sạch bã nhờn sâu trong lỗ chân lông, giảm bóng dầu mà không gây khô căng da.',
                'ingredients' => 'Aqua / Water, Kaolin, Argilla / Magnesium Aluminum Silicate, Propanediol, Panthenol, Glycerin, Caprylic/Capric Triglyceride, Allantoin.',
                'skin_type' => 'Da dầu',
                'volume' => '100ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP027',
                'category_id' => $catSkincare,
                'brand_id' => $brandSulwhasoo,
                'name' => 'Dầu Tẩy Trang Thảo Mộc Làm Sạch Sâu Sulwhasoo Gentle Cleansing Oil',
                'slug' => 'dau-tay-trang-thao-moc-lam-sach-sau-sulwhasoo-gentle-cleansing-oil',
                'price' => 890000,
                'sale_price' => 760000,
                'stock' => 42,
                'images' => [
                    'https://images.unsplash.com/photo-1612817288484-6f916006741a?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1617897903246-719242758050?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Dầu tẩy trang chiết xuất hạt Ý Dĩ, vỏ Quýt Trần Bì và dầu hạt Mơ giúp nhũ hóa cực nhanh, cuốn trôi lớp makeup chống nước và giữ lại màng ẩm mềm mại.',
                'ingredients' => 'Cetyl Ethylhexanoate, Isopropyl Palmitate, PEG-20 Glyceryl Triisostearate, Prunus Armeniaca Kernel Oil, Coix Lacryma-Jobi Ma-yuen Seed Extract.',
                'skin_type' => 'Mọi loại da',
                'volume' => '200ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP028',
                'category_id' => $catSkincare,
                'brand_id' => $brandShiseido,
                'name' => 'Sữa Dưỡng Nâng Cơ & Săn Chắc Da Shiseido Vital Perfection Emulsion',
                'slug' => 'sua-duong-nang-co-san-chac-da-shiseido-vital-perfection-emulsion',
                'price' => 2150000,
                'sale_price' => 1850000,
                'stock' => 28,
                'images' => [
                    'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Sữa dưỡng chống lão hóa cao cấp ứng dụng công nghệ ReNeura++™ và phức hợp KURENAI-TruLift cùng 4MSK giúp dưỡng sáng đều màu và nâng cơ thon gọn.',
                'ingredients' => 'Water, Dipropylene Glycol, Glycerin, Potassium Methoxysalicylate (4MSK), Carthamus Tinctorius Flower Extract, Angelica Keiskei Leaf/Stem Extract.',
                'skin_type' => 'Da khô',
                'volume' => '100ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP029',
                'category_id' => $catMakeup,
                'brand_id' => $brandLancome,
                'name' => 'Kem Nền Mỏng Nhẹ Lâu Trôi Lancôme Teint Idole Ultra Wear SPF38',
                'slug' => 'kem-nen-mong-nhe-lau-troi-lancome-teint-idole-ultra-wear-spf38',
                'price' => 1450000,
                'sale_price' => 1220000,
                'stock' => 45,
                'images' => [
                    'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Kem nền che phủ hoàn hảo với công nghệ Advanced Airwear™ mỏng nhẹ như không khí, kiềm dầu bền màu suốt 24 giờ và bổ sung Hyaluronic Acid cấp ẩm.',
                'ingredients' => 'Aqua / Water, Dimethicone, Isododecane, Alcohol Denat., Trimethylsiloxysilicate, Titanium Dioxide, Sodium Hyaluronate, Moringa Oleifera Seed Extract.',
                'skin_type' => 'Da dầu',
                'volume' => '30ml',
                'is_best_seller' => true,
            ],
            [
                'sku' => 'LMN-SP030',
                'category_id' => $catMakeup,
                'brand_id' => $brandShiseido,
                'name' => 'Phấn Phủ Bột Kiềm Dầu Trong Suốt Shiseido Synchro Skin Loose Powder',
                'slug' => 'phan-phu-bot-kiem-dau-trong-suot-shiseido-synchro-skin-loose-powder',
                'price' => 980000,
                'sale_price' => 830000,
                'stock' => 50,
                'images' => [
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Phấn phủ dạng bột siêu mịn với hạt phấn bọc dưỡng chất tự đồng bộ theo độ ẩm của da, làm mờ lỗ chân lông tức thì và khóa lớp nền mịn lì suốt 8 giờ.',
                'ingredients' => 'Silica, Synthetic Fluorphlogopite, Mica, Cellulose, Dimethicone, Thymus Serpyllum Extract, Tocopherol, Water.',
                'skin_type' => 'Da dầu',
                'volume' => '6g',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP031',
                'category_id' => $catMakeup,
                'brand_id' => $brandLancome,
                'name' => 'Mascara Làm Dày & Cong Mi Chống Nước Lancôme Lash Idôle Waterproof',
                'slug' => 'mascara-lam-day-cong-mi-chong-nuoc-lancome-lash-idole-waterproof',
                'price' => 820000,
                'sale_price' => 690000,
                'stock' => 55,
                'images' => [
                    'https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Đầu cọ cong đàn hồi 360 sợi lông vi điểm kết hợp chiết xuất Trà Trắng giúp chuốt tơi từng sợi mi cong vút, không vón cục và không lem trôi suốt 24 giờ.',
                'ingredients' => 'Isododecane, Cera Alba / Beeswax, Copernicia Cerifera Cera / Carnauba Wax, Disteardimonium Hectorite, Camellia Sinensis Leaf Extract, Panthenol.',
                'skin_type' => 'Mọi loại da',
                'volume' => '8ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP032',
                'category_id' => $catMakeup,
                'brand_id' => $brandSulwhasoo,
                'name' => 'Phấn Má Hồng Ánh Ngọc Trai Sulwhasoo Radiance Blusher Đa Sắc',
                'slug' => 'phan-ma-hong-anh-ngoc-trai-sulwhasoo-radiance-blusher-da-sac',
                'price' => 1120000,
                'sale_price' => 950000,
                'stock' => 38,
                'images' => [
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1612817288484-6f916006741a?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Phấn má hồng họa tiết hoa mơ truyền thống kết hợp 3 sắc độ hồng san hô và phấn bắt sáng ngọc trai tạo hiệu ứng đôi gò má ửng hồng căng bóng tự nhiên.',
                'ingredients' => 'Talc, Mica, Titanium Dioxide, Synthetic Fluorphlogopite, Prunus Mume Flower Extract, Pearl Powder, Squalane, Dimethicone.',
                'skin_type' => 'Mọi loại da',
                'volume' => '10g',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP033',
                'category_id' => $catMakeup,
                'brand_id' => $brandLaRoche,
                'name' => 'Kem Lót Nâng Tông Bảo Vệ Da La Roche-Posay Uvidea Anthelios Tone-Up Rosy',
                'slug' => 'kem-lot-nang-tong-bao-ve-da-la-roche-posay-uvidea-tone-up-rosy',
                'price' => 620000,
                'sale_price' => 520000,
                'stock' => 60,
                'images' => [
                    'https://images.unsplash.com/photo-1617897903246-719242758050?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Kem lót trang điểm kiêm chống nắng SPF50+ PA++++ sắc hồng ngọc trai giúp hiệu chỉnh làn da xỉn màu trở nên rạng rỡ, giữ lớp nền bám chặt suốt ngày dài.',
                'ingredients' => 'Aqua / Water, Ethylhexyl Methoxycinnamate, Titanium Dioxide, Glycerin, Drometrizole Trisiloxane, Tocopherol, Paeonia Suffruticosa Root Extract.',
                'skin_type' => 'Da nhạy cảm',
                'volume' => '30ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP034',
                'category_id' => $catBodycare,
                'brand_id' => $brandSulwhasoo,
                'name' => 'Dầu Dưỡng Thể Thảo Mộc Thư Giãn Sulwhasoo White Breath Body Oil',
                'slug' => 'dau-duong-the-thao-moc-thu-gian-sulwhasoo-white-breath-body-oil',
                'price' => 950000,
                'sale_price' => 790000,
                'stock' => 36,
                'images' => [
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1612817288484-6f916006741a?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Dầu dưỡng thể giàu tinh dầu Hạt Thông, Hạt Mè và Hoa Mai Trắng thấm nhanh vào da sau khi tắm, khóa ẩm chuyên sâu và mang lại hương thơm spa thư thái.',
                'ingredients' => 'Helianthus Annuus Seed Oil, Prunus Armeniaca Kernel Oil, Pinus Koraiensis Seed Oil, Sesamum Indicum Seed Oil, Prunus Mume Flower Extract.',
                'skin_type' => 'Da khô',
                'volume' => '250ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP035',
                'category_id' => $catBodycare,
                'brand_id' => $brandLaRoche,
                'name' => 'Gel Tắm Làm Dịu Da Khô Nhạy Cảm La Roche-Posay Lipikar Syndet AP+',
                'slug' => 'gel-tam-lam-diu-da-kho-nhay-cam-la-roche-posay-lipikar-syndet-ap',
                'price' => 560000,
                'sale_price' => 465000,
                'stock' => 65,
                'images' => [
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Kem tắm không xà phòng, không hương liệu chứa Aqua Posae Filiformis, Niacinamide và Bơ Hạt Mỡ giúp giảm ngứa rát, phục hồi màng ẩm tự nhiên ngay khi tắm.',
                'ingredients' => 'Aqua / Water, Glycerin, Sodium Laureth Sulfate, PEG-200 Hydrogenated Glyceryl Palmate, Shea Butter, Niacinamide, Vitreoscilla Ferment.',
                'skin_type' => 'Da nhạy cảm',
                'volume' => '400ml',
                'is_best_seller' => true,
            ],
            [
                'sku' => 'LMN-SP036',
                'category_id' => $catBodycare,
                'brand_id' => $brandLancome,
                'name' => 'Sữa Dưỡng Thể Hương Nước Hoa Lancôme La Vie Est Belle Body Lotion',
                'slug' => 'sua-duong-the-huong-nuoc-hoa-lancome-la-vie-est-belle-body-lotion',
                'price' => 1180000,
                'sale_price' => 990000,
                'stock' => 40,
                'images' => [
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Sữa dưỡng thể cao cấp chiết xuất Sữa Ong Chúa kết hợp hương hoa Diên Vĩ và Vani ngọt ngào từ dòng nước hoa biểu tượng La Vie Est Belle, giúp da mềm mượt tỏa hương.',
                'ingredients' => 'Aqua / Water, Hydrogenated Polyisobutene, Paraffinum Liquidum, Glycerin, Butyrospermum Parkii Butter, Parfum / Fragrance, Royal Jelly Extract.',
                'skin_type' => 'Mọi loại da',
                'volume' => '200ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP037',
                'category_id' => $catBodycare,
                'brand_id' => $brandShiseido,
                'name' => 'Sữa Chống Nắng Toàn Thân Kháng Nước Shiseido Perfect UV Skincare Milk',
                'slug' => 'sua-chong-nang-toan-than-khang-nuoc-shiseido-perfect-uv-skincare-milk',
                'price' => 680000,
                'sale_price' => 560000,
                'stock' => 70,
                'images' => [
                    'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Sữa chống nắng cho mặt và toàn thân ứng dụng công nghệ Auto Booster tăng cường màng chắn UV khi gặp mồ hôi và nước, chứa 50% thành phần dưỡng da khô thoáng.',
                'ingredients' => 'Dimethicone, Water, Zinc Oxide, Ethylhexyl Methoxycinnamate, Alcohol, Isododecane, Camellia Sinensis Leaf Extract, Sodium Acetylated Hyaluronate.',
                'skin_type' => 'Da dầu',
                'volume' => '60ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP038',
                'category_id' => $catBodycare,
                'brand_id' => $brandSulwhasoo,
                'name' => 'Xà Phòng Thảo Dược Nhân Sâm Cao Cấp Sulwhasoo Herbal Soap',
                'slug' => 'xa-phong-thao-duoc-nhan-sam-cao-cap-sulwhasoo-herbal-soap',
                'price' => 450000,
                'sale_price' => 370000,
                'stock' => 55,
                'images' => [
                    'https://images.unsplash.com/photo-1612817288484-6f916006741a?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Bánh xà phòng thảo dược ủ thủ công suốt 40 ngày với Hồng Sâm 6 năm tuổi, Ngọc Trúc và Địa Hoàng giúp làm sạch thanh khiết cho cả vùng mặt và cơ thể.',
                'ingredients' => 'Sodium Palmate, Sodium Palm Kernelate, Water, Panax Ginseng Root Extract, Polygonatum Officinale Rhizome/Root Extract, Rehmannia Glutinosa Root Extract.',
                'skin_type' => 'Mọi loại da',
                'volume' => '100g',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP039',
                'category_id' => $catPerfume,
                'brand_id' => $brandLancome,
                'name' => 'Nước Hoa Nữ Lancôme Idôle Le Parfum Hương Hoa Hồng & Xạ Hương Trắng',
                'slug' => 'nuoc-hoa-nu-lancome-idole-le-parfum-huong-hoa-hong-xa-huong-trang',
                'price' => 2650000,
                'sale_price' => 2290000,
                'stock' => 30,
                'images' => [
                    'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Thiết kế chai mỏng nhất thế giới mang tuyên ngôn nữ quyền hiện đại với sự hòa quyện của 4 loài hoa hồng rực rỡ, hoa nhài Ấn Độ và tầng hương Chypre trắng tinh khôi.',
                'ingredients' => 'Alcohol, Parfum / Fragrance, Aqua / Water, Limonene, Linalool, Citronellol, Benzyl Salicylate, Geraniol, Coumarin.',
                'skin_type' => 'Mọi loại da',
                'volume' => '50ml',
                'is_best_seller' => true,
            ],
            [
                'sku' => 'LMN-SP040',
                'category_id' => $catPerfume,
                'brand_id' => $brandShiseido,
                'name' => 'Nước Hoa Nữ Shiseido Ginza Eau de Parfum Hương Hoa Nhài & Gỗ Hinoki',
                'slug' => 'nuoc-hoa-nu-shiseido-ginza-eau-de-parfum-huong-hoa-nhai-go-hinoki',
                'price' => 2350000,
                'sale_price' => 1980000,
                'stock' => 32,
                'images' => [
                    'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Lấy cảm hứng từ khu phố sầm uất Ginza Tokyo, kết hợp sự thanh lịch của hoa Nhài, Mộc Lan, Lan Nam Phi cùng chiều sâu ấm áp của gỗ Bách Nhật Hinoki thiêng liêng.',
                'ingredients' => 'Alcohol, Fragrance (Parfum), Water (Aqua/Eau), Benzyl Salicylate, Limonene, Linalool, Hydroxycitronellal, Hexyl Cinnamal.',
                'skin_type' => 'Mọi loại da',
                'volume' => '50ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP041',
                'category_id' => $catPerfume,
                'brand_id' => $brandLancome,
                'name' => 'Nước Hoa Nữ Lancôme Trésor Midnight Rose Eau de Parfum Quyến Rũ',
                'slug' => 'nuoc-hoa-nu-lancome-tresor-midnight-rose-eau-de-parfum-quyen-ru',
                'price' => 2490000,
                'sale_price' => 2120000,
                'stock' => 28,
                'images' => [
                    'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Bản tình ca lãng mạn giữa lòng Paris lúc nửa đêm với nốt hương Quả Mâm Xôi ngọt ngào, Hoa Hồng Damask nồng nàn cùng Xạ Hương, Tuyết Tùng và Vani ấm áp.',
                'ingredients' => 'Alcohol, Parfum / Fragrance, Aqua / Water, Ethylhexyl Methoxycinnamate, Linalool, Geraniol, Eugenol, Alpha-Isomethyl Ionone.',
                'skin_type' => 'Mọi loại da',
                'volume' => '50ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP042',
                'category_id' => $catPerfume,
                'brand_id' => $brandSulwhasoo,
                'name' => 'Nước Hoa Thảo Mộc Phương Đông Sulwhasoo First Peace Eau de Parfum',
                'slug' => 'nuoc-hoa-thao-moc-phuong-dong-sulwhasoo-first-peace-eau-de-parfum',
                'price' => 2580000,
                'sale_price' => 2190000,
                'stock' => 25,
                'images' => [
                    'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1612817288484-6f916006741a?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Nước hoa nghệ thuật phương Đông hòa quyện hương gỗ Bách Tùng thanh tịnh, Hoa Mai Trắng dưới sương sớm và Trầm Hương dịu nhẹ giúp cân bằng tâm trí.',
                'ingredients' => 'Alcohol Denat., Fragrance / Parfum, Water / Aqua, Limonene, Linalool, Prunus Mume Flower Extract, Panax Ginseng Root Water.',
                'skin_type' => 'Mọi loại da',
                'volume' => '50ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP043',
                'category_id' => $catPerfume,
                'brand_id' => $brandShiseido,
                'name' => 'Nước Hoa Nữ Shiseido Ever Bloom Eau de Parfum Hương Hoa Cam & Bạch Liên',
                'slug' => 'nuoc-hoa-nu-shiseido-ever-bloom-eau-de-parfum-huong-hoa-cam-bach-lien',
                'price' => 2190000,
                'sale_price' => 1850000,
                'stock' => 34,
                'images' => [
                    'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Hương hoa trắng thanh khiết tỏa sáng từ Hoa Sen Trắng, Hoa Dành Dành (Gardenia), Hoa Cam Tunisia cùng gỗ Hinoki và xạ hương Sylkolide dịu dàng.',
                'ingredients' => 'Alcohol, Fragrance (Parfum), Aqua / Water, Dipropylene Glycol, Butyl Methoxydibenzoylmethane, Limonene, Citronellol.',
                'skin_type' => 'Mọi loại da',
                'volume' => '50ml',
                'is_best_seller' => false,
            ],
            [
                'sku' => 'LMN-SP044',
                'category_id' => $catPerfume,
                'brand_id' => $brandLancome,
                'name' => 'Nước Hoa Nữ Lancôme Miracle Eau de Parfum Hương Vải & Hoa Mộc Lan',
                'slug' => 'nuoc-hoa-nu-lancome-miracle-eau-de-parfum-huong-vai-hoa-moc-lan',
                'price' => 1350000,
                'sale_price' => 1150000,
                'stock' => 45,
                'images' => [
                    'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Mùi hương tươi mới tràn đầy năng lượng ban mai từ trái Vải mọng nước, Hoa Lan Nam Phi, Hoa Mộc Lan kết hợp chút Gừng ấm áp và Hổ Phách thanh lịch.',
                'ingredients' => 'Alcohol, Parfum / Fragrance, Aqua / Water, Linalool, Geraniol, Isoeugenol, Cinnamyl Alcohol, Limonene, Hydroxycitronellal.',
                'skin_type' => 'Mọi loại da',
                'volume' => '30ml',
                'is_best_seller' => false,
            ],
        ];

        foreach ($newProducts as $prod) {
            self::query()->updateOrCreate(['slug' => $prod['slug']], $prod);
        }
    }
}
