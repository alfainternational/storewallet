<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Electronics',
                'name_ar' => 'الإلكترونيات',
                'slug' => 'electronics',
                'description' => 'Electronic devices and gadgets',
                'description_ar' => 'الأجهزة الإلكترونية والتقنية',
                'icon' => 'laptop',
                'is_featured' => true,
                'subcategories' => [
                    ['name' => 'Mobile Phones', 'name_ar' => 'الهواتف المحمولة', 'slug' => 'mobile-phones'],
                    ['name' => 'Laptops', 'name_ar' => 'أجهزة الكمبيوتر المحمول', 'slug' => 'laptops'],
                    ['name' => 'Tablets', 'name_ar' => 'الأجهزة اللوحية', 'slug' => 'tablets'],
                    ['name' => 'Cameras', 'name_ar' => 'الكاميرات', 'slug' => 'cameras'],
                    ['name' => 'Headphones', 'name_ar' => 'سماعات الرأس', 'slug' => 'headphones'],
                ]
            ],
            [
                'name' => 'Fashion',
                'name_ar' => 'الأزياء',
                'slug' => 'fashion',
                'description' => 'Clothing and accessories',
                'description_ar' => 'الملابس والإكسسوارات',
                'icon' => 'tshirt',
                'is_featured' => true,
                'subcategories' => [
                    ['name' => 'Men\'s Clothing', 'name_ar' => 'ملابس رجالية', 'slug' => 'mens-clothing'],
                    ['name' => 'Women\'s Clothing', 'name_ar' => 'ملابس نسائية', 'slug' => 'womens-clothing'],
                    ['name' => 'Shoes', 'name_ar' => 'الأحذية', 'slug' => 'shoes'],
                    ['name' => 'Bags', 'name_ar' => 'الحقائب', 'slug' => 'bags'],
                    ['name' => 'Accessories', 'name_ar' => 'الإكسسوارات', 'slug' => 'accessories'],
                ]
            ],
            [
                'name' => 'Home & Garden',
                'name_ar' => 'المنزل والحديقة',
                'slug' => 'home-garden',
                'description' => 'Home furniture and garden supplies',
                'description_ar' => 'أثاث المنزل ومستلزمات الحديقة',
                'icon' => 'home',
                'is_featured' => true,
                'subcategories' => [
                    ['name' => 'Furniture', 'name_ar' => 'الأثاث', 'slug' => 'furniture'],
                    ['name' => 'Kitchen', 'name_ar' => 'المطبخ', 'slug' => 'kitchen'],
                    ['name' => 'Bedding', 'name_ar' => 'مفروشات السرير', 'slug' => 'bedding'],
                    ['name' => 'Garden Tools', 'name_ar' => 'أدوات الحديقة', 'slug' => 'garden-tools'],
                    ['name' => 'Lighting', 'name_ar' => 'الإضاءة', 'slug' => 'lighting'],
                ]
            ],
            [
                'name' => 'Sports & Outdoors',
                'name_ar' => 'الرياضة والأنشطة الخارجية',
                'slug' => 'sports-outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'description_ar' => 'معدات رياضية ومستلزمات الأنشطة الخارجية',
                'icon' => 'football',
                'subcategories' => [
                    ['name' => 'Fitness', 'name_ar' => 'اللياقة البدنية', 'slug' => 'fitness'],
                    ['name' => 'Football', 'name_ar' => 'كرة القدم', 'slug' => 'football'],
                    ['name' => 'Cycling', 'name_ar' => 'ركوب الدراجات', 'slug' => 'cycling'],
                    ['name' => 'Camping', 'name_ar' => 'التخييم', 'slug' => 'camping'],
                ]
            ],
            [
                'name' => 'Books & Media',
                'name_ar' => 'الكتب والإعلام',
                'slug' => 'books-media',
                'description' => 'Books, music and entertainment',
                'description_ar' => 'الكتب والموسيقى والترفيه',
                'icon' => 'book',
                'subcategories' => [
                    ['name' => 'Books', 'name_ar' => 'الكتب', 'slug' => 'books'],
                    ['name' => 'Movies', 'name_ar' => 'الأفلام', 'slug' => 'movies'],
                    ['name' => 'Music', 'name_ar' => 'الموسيقى', 'slug' => 'music'],
                    ['name' => 'Games', 'name_ar' => 'الألعاب', 'slug' => 'games'],
                ]
            ],
            [
                'name' => 'Food & Beverages',
                'name_ar' => 'الأطعمة والمشروبات',
                'slug' => 'food-beverages',
                'description' => 'Food, drinks and groceries',
                'description_ar' => 'الأطعمة والمشروبات والبقالة',
                'icon' => 'utensils',
                'is_featured' => true,
                'subcategories' => [
                    ['name' => 'Fresh Produce', 'name_ar' => 'المنتجات الطازجة', 'slug' => 'fresh-produce'],
                    ['name' => 'Snacks', 'name_ar' => 'الوجبات الخفيفة', 'slug' => 'snacks'],
                    ['name' => 'Beverages', 'name_ar' => 'المشروبات', 'slug' => 'beverages'],
                    ['name' => 'Spices', 'name_ar' => 'التوابل', 'slug' => 'spices'],
                ]
            ],
            [
                'name' => 'Beauty & Health',
                'name_ar' => 'الجمال والصحة',
                'slug' => 'beauty-health',
                'description' => 'Beauty products and health items',
                'description_ar' => 'منتجات التجميل والصحة',
                'icon' => 'heart',
                'subcategories' => [
                    ['name' => 'Skincare', 'name_ar' => 'العناية بالبشرة', 'slug' => 'skincare'],
                    ['name' => 'Makeup', 'name_ar' => 'المكياج', 'slug' => 'makeup'],
                    ['name' => 'Fragrances', 'name_ar' => 'العطور', 'slug' => 'fragrances'],
                    ['name' => 'Healthcare', 'name_ar' => 'الرعاية الصحية', 'slug' => 'healthcare'],
                ]
            ],
            [
                'name' => 'Baby & Kids',
                'name_ar' => 'الأطفال والرضع',
                'slug' => 'baby-kids',
                'description' => 'Products for babies and children',
                'description_ar' => 'منتجات للأطفال والرضع',
                'icon' => 'baby',
                'subcategories' => [
                    ['name' => 'Baby Care', 'name_ar' => 'العناية بالرضع', 'slug' => 'baby-care'],
                    ['name' => 'Toys', 'name_ar' => 'الألعاب', 'slug' => 'toys'],
                    ['name' => 'Strollers', 'name_ar' => 'عربات الأطفال', 'slug' => 'strollers'],
                    ['name' => 'Kids Fashion', 'name_ar' => 'أزياء الأطفال', 'slug' => 'kids-fashion'],
                ]
            ],
            [
                'name' => 'Automotive',
                'name_ar' => 'السيارات',
                'slug' => 'automotive',
                'description' => 'Car parts and accessories',
                'description_ar' => 'قطع غيار ومستلزمات السيارات',
                'icon' => 'car',
                'subcategories' => [
                    ['name' => 'Car Parts', 'name_ar' => 'قطع الغيار', 'slug' => 'car-parts'],
                    ['name' => 'Car Accessories', 'name_ar' => 'إكسسوارات السيارات', 'slug' => 'car-accessories'],
                    ['name' => 'Tools', 'name_ar' => 'الأدوات', 'slug' => 'tools'],
                ]
            ],
            [
                'name' => 'Stationery & Office',
                'name_ar' => 'القرطاسية والمكتب',
                'slug' => 'stationery-office',
                'description' => 'Office supplies and stationery',
                'description_ar' => 'مستلزمات المكاتب والقرطاسية',
                'icon' => 'pen',
                'subcategories' => [
                    ['name' => 'Writing', 'name_ar' => 'أدوات الكتابة', 'slug' => 'writing'],
                    ['name' => 'Paper', 'name_ar' => 'الورق', 'slug' => 'paper'],
                    ['name' => 'Office Equipment', 'name_ar' => 'معدات المكتب', 'slug' => 'office-equipment'],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $subcategories = $categoryData['subcategories'] ?? [];
            unset($categoryData['subcategories']);

            $category = Category::create($categoryData);

            foreach ($subcategories as $subData) {
                Category::create(array_merge($subData, [
                    'parent_id' => $category->id,
                    'description' => $subData['name'],
                    'description_ar' => $subData['name_ar'],
                ]));
            }
        }
    }
}
