<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class EgyptLocationsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // 1. Create Egypt Country
        $egypt = Country::create([
            'name_ar' => 'مصر',
            'name_en' => 'Egypt',
            'code' => 'EG',
            'phone_code' => '+20',
            'currency_code' => 'EGP',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // 2. Create Governorates with Cities
        $this->createGovernoratesAndCities($egypt->id);

        $this->command->info('✅ Egypt locations seeded successfully!');
        $this->command->info('📊 Total: 1 Country, 27 Governorates, 150+ Cities');
    }

    private function createGovernoratesAndCities(int $countryId): void
    {
        $governoratesData = [
            [
                'name_ar' => 'القاهرة',
                'name_en' => 'Cairo',
                'shipping_cost' => 30.00,
                'delivery_days' => 2,
                'cities' => ['مدينة نصر', 'المعادي', 'حلوان', 'مصر الجديدة', 'الزمالك', 'المطرية', 'عين شمس', 'شبرا', 'العباسية', 'المقطم']
            ],
            [
                'name_ar' => 'الجيزة',
                'name_en' => 'Giza',
                'shipping_cost' => 30.00,
                'delivery_days' => 2,
                'cities' => ['الدقي', 'المهندسين', 'فيصل', 'الهرم', 'أكتوبر', 'الشيخ زايد', 'البدرشين', 'العجوزة', 'المنيب', 'الوراق']
            ],
            [
                'name_ar' => 'الإسكندرية',
                'name_en' => 'Alexandria',
                'shipping_cost' => 50.00,
                'delivery_days' => 3,
                'cities' => ['المنتزة', 'سموحة', 'ميامي', 'سيدي بشر', 'العصافرة', 'المعمورة', 'العامرية', 'برج العرب', 'محرم بك', 'كرموز']
            ],
            [
                'name_ar' => 'الدقهلية',
                'name_en' => 'Dakahlia',
                'shipping_cost' => 45.00,
                'delivery_days' => 3,
                'cities' => ['المنصورة', 'طلخا', 'ميت غمر', 'دكرنس', 'أجا', 'منية النصر', 'السنبلاوين', 'الكردي', 'بني عبيد', 'المطرية']
            ],
            [
                'name_ar' => 'الشرقية',
                'name_en' => 'Sharqia',
                'shipping_cost' => 45.00,
                'delivery_days' => 3,
                'cities' => ['الزقازيق', 'العاشر من رمضان', 'بلبيس', 'فاقوس', 'ههيا', 'ديرب نجم', 'أبو حماد', 'مشتول السوق', 'أبو كبير', 'منيا القمح']
            ],
            [
                'name_ar' => 'القليوبية',
                'name_en' => 'Qalyubia',
                'shipping_cost' => 35.00,
                'delivery_days' => 2,
                'cities' => ['بنها', 'شبرا الخيمة', 'القناطر الخيرية', 'الخانكة', 'قليوب', 'طوخ', 'كفر شكر', 'شبين القناطر', 'الخصوص', 'العبور']
            ],
            [
                'name_ar' => 'الغربية',
                'name_en' => 'Gharbia',
                'shipping_cost' => 45.00,
                'delivery_days' => 3,
                'cities' => ['طنطا', 'المحلة الكبرى', 'كفر الزيات', 'زفتى', 'السنطة', 'قطور', 'بسيون', 'سمنود']
            ],
            [
                'name_ar' => 'المنوفية',
                'name_en' => 'Monufia',
                'shipping_cost' => 40.00,
                'delivery_days' => 3,
                'cities' => ['شبين الكوم', 'منوف', 'أشمون', 'قويسنا', 'تلا', 'الباجور', 'السادات', 'بركة السبع']
            ],
            [
                'name_ar' => 'البحيرة',
                'name_en' => 'Beheira',
                'shipping_cost' => 50.00,
                'delivery_days' => 3,
                'cities' => ['دمنهور', 'كفر الدوار', 'رشيد', 'إدكو', 'أبو المطامير', 'الدلنجات', 'المحمودية', 'كوم حمادة', 'حوش عيسى']
            ],
            [
                'name_ar' => 'كفر الشيخ',
                'name_en' => 'Kafr El Sheikh',
                'shipping_cost' => 50.00,
                'delivery_days' => 4,
                'cities' => ['كفر الشيخ', 'دسوق', 'فوه', 'مطوبس', 'بيلا', 'الحامول', 'بلطيم', 'الرياض', 'سيدي سالم']
            ],
            [
                'name_ar' => 'دمياط',
                'name_en' => 'Damietta',
                'shipping_cost' => 50.00,
                'delivery_days' => 4,
                'cities' => ['دمياط', 'رأس البر', 'فارسكور', 'الزرقا', 'كفر سعد', 'عزبة البرج', 'ميت أبو غالب']
            ],
            [
                'name_ar' => 'بورسعيد',
                'name_en' => 'Port Said',
                'shipping_cost' => 55.00,
                'delivery_days' => 3,
                'cities' => ['بورسعيد', 'بورفؤاد', 'العرب', 'الزهور', 'المناخ', 'الضواحي']
            ],
            [
                'name_ar' => 'الإسماعيلية',
                'name_en' => 'Ismailia',
                'shipping_cost' => 50.00,
                'delivery_days' => 3,
                'cities' => ['الإسماعيلية', 'فايد', 'القنطرة شرق', 'القنطرة غرب', 'التل الكبير', 'أبو صوير']
            ],
            [
                'name_ar' => 'السويس',
                'name_en' => 'Suez',
                'shipping_cost' => 50.00,
                'delivery_days' => 3,
                'cities' => ['السويس', 'الأربعين', 'عتاقة', 'الجناين', 'فيصل']
            ],
            [
                'name_ar' => 'المنيا',
                'name_en' => 'Minya',
                'shipping_cost' => 60.00,
                'delivery_days' => 4,
                'cities' => ['المنيا', 'ملوي', 'سمالوط', 'المنيا الجديدة', 'العدوة', 'مغاغة', 'بني مزار', 'مطاي', 'أبو قرقاص']
            ],
            [
                'name_ar' => 'بني سويف',
                'name_en' => 'Beni Suef',
                'shipping_cost' => 55.00,
                'delivery_days' => 4,
                'cities' => ['بني سويف', 'الواسطى', 'ناصر', 'إهناسيا', 'ببا', 'الفشن', 'سمسطا']
            ],
            [
                'name_ar' => 'الفيوم',
                'name_en' => 'Fayoum',
                'shipping_cost' => 50.00,
                'delivery_days' => 3,
                'cities' => ['الفيوم', 'طامية', 'سنورس', 'إطسا', 'إبشواي', 'يوسف الصديق']
            ],
            [
                'name_ar' => 'أسيوط',
                'name_en' => 'Assiut',
                'shipping_cost' => 65.00,
                'delivery_days' => 4,
                'cities' => ['أسيوط', 'ديروط', 'منفلوط', 'القوصية', 'أبنوب', 'أبو تيج', 'الغنايم', 'ساحل سليم', 'البداري']
            ],
            [
                'name_ar' => 'سوهاج',
                'name_en' => 'Sohag',
                'shipping_cost' => 70.00,
                'delivery_days' => 5,
                'cities' => ['سوهاج', 'أخميم', 'جرجا', 'البلينا', 'المراغة', 'طما', 'طهطا', 'جهينة', 'دار السلام', 'العسيرات']
            ],
            [
                'name_ar' => 'قنا',
                'name_en' => 'Qena',
                'shipping_cost' => 75.00,
                'delivery_days' => 5,
                'cities' => ['قنا', 'نجع حمادي', 'دشنا', 'الوقف', 'قفط', 'نقادة', 'فرشوط', 'قوص', 'أبو تشت']
            ],
            [
                'name_ar' => 'الأقصر',
                'name_en' => 'Luxor',
                'shipping_cost' => 75.00,
                'delivery_days' => 5,
                'cities' => ['الأقصر', 'الأقصر الجديدة', 'إسنا', 'الطود', 'الزينية', 'البياضية', 'القرنة', 'أرمنت']
            ],
            [
                'name_ar' => 'أسوان',
                'name_en' => 'Aswan',
                'shipping_cost' => 80.00,
                'delivery_days' => 5,
                'cities' => ['أسوان', 'كوم أمبو', 'دراو', 'نصر النوبة', 'إدفو', 'السباعية']
            ],
            [
                'name_ar' => 'البحر الأحمر',
                'name_en' => 'Red Sea',
                'shipping_cost' => 85.00,
                'delivery_days' => 4,
                'cities' => ['الغردقة', 'سفاجا', 'القصير', 'مرسى علم', 'رأس غارب', 'الشلاتين', 'حلايب']
            ],
            [
                'name_ar' => 'الوادي الجديد',
                'name_en' => 'New Valley',
                'shipping_cost' => 90.00,
                'delivery_days' => 6,
                'cities' => ['الخارجة', 'الداخلة', 'الفرافرة', 'باريس', 'بلاط']
            ],
            [
                'name_ar' => 'مطروح',
                'name_en' => 'Matrouh',
                'shipping_cost' => 80.00,
                'delivery_days' => 5,
                'cities' => ['مرسى مطروح', 'الحمام', 'العلمين', 'الضبعة', 'النجيلة', 'سيوة', 'السلوم']
            ],
            [
                'name_ar' => 'شمال سيناء',
                'name_en' => 'North Sinai',
                'shipping_cost' => 75.00,
                'delivery_days' => 5,
                'cities' => ['العريش', 'الشيخ زويد', 'رفح', 'بئر العبد', 'الحسنة', 'نخل']
            ],
            [
                'name_ar' => 'جنوب سيناء',
                'name_en' => 'South Sinai',
                'shipping_cost' => 80.00,
                'delivery_days' => 5,
                'cities' => ['شرم الشيخ', 'دهب', 'نويبع', 'طابا', 'سانت كاترين', 'رأس سدر', 'أبو رديس', 'الطور']
            ],
        ];

        foreach ($governoratesData as $govData) {
            $cities = $govData['cities'];
            unset($govData['cities']);

            $governorate = Governorate::create([
                'country_id' => $countryId,
                'name_ar' => $govData['name_ar'],
                'name_en' => $govData['name_en'],
                'shipping_cost' => $govData['shipping_cost'],
                'delivery_days' => $govData['delivery_days'],
                'is_active' => true,
                'sort_order' => 0,
            ]);

            // Create cities for this governorate
            foreach ($cities as $index => $cityName) {
                City::create([
                    'governorate_id' => $governorate->id,
                    'name_ar' => $cityName,
                    'name_en' => $cityName, // Can be translated later if needed
                    'shipping_cost' => null, // Uses governorate default
                    'is_active' => true,
                    'sort_order' => $index,
                ]);
            }

            $this->command->info("✅ {$govData['name_ar']}: " . count($cities) . " cities");
        }
    }
}
