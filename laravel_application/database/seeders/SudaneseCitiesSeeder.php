<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SudaneseCity;

class SudaneseCitiesSeeder extends Seeder
{
    public function run()
    {
        $cities = [
            // Khartoum State
            ['name' => 'Khartoum', 'name_ar' => 'الخرطوم', 'state' => 'Khartoum', 'latitude' => 15.5007, 'longitude' => 32.5599],
            ['name' => 'Omdurman', 'name_ar' => 'أم درمان', 'state' => 'Khartoum', 'latitude' => 15.6446, 'longitude' => 32.4777],
            ['name' => 'Bahri', 'name_ar' => 'بحري', 'state' => 'Khartoum', 'latitude' => 15.6364, 'longitude' => 32.5364],
            ['name' => 'Khartoum North', 'name_ar' => 'الخرطوم بحري', 'state' => 'Khartoum', 'latitude' => 15.6167, 'longitude' => 32.5333],

            // Northern State
            ['name' => 'Dongola', 'name_ar' => 'دنقلا', 'state' => 'Northern', 'latitude' => 19.1666, 'longitude' => 30.4833],
            ['name' => 'Karima', 'name_ar' => 'كريمة', 'state' => 'Northern', 'latitude' => 18.5500, 'longitude' => 31.8500],
            ['name' => 'Merowe', 'name_ar' => 'مروي', 'state' => 'Northern', 'latitude' => 18.5167, 'longitude' => 31.8167],
            ['name' => 'Atbara', 'name_ar' => 'عطبرة', 'state' => 'Northern', 'latitude' => 17.7023, 'longitude' => 33.9896],
            ['name' => 'Wadi Halfa', 'name_ar' => 'وادي حلفا', 'state' => 'Northern', 'latitude' => 21.8000, 'longitude' => 31.3500],

            // River Nile State
            ['name' => 'Ed Damer', 'name_ar' => 'الدامر', 'state' => 'River Nile', 'latitude' => 17.5939, 'longitude' => 33.9591],
            ['name' => 'Berber', 'name_ar' => 'بربر', 'state' => 'River Nile', 'latitude' => 18.0167, 'longitude' => 33.9833],
            ['name' => 'Shendi', 'name_ar' => 'شندي', 'state' => 'River Nile', 'latitude' => 16.6917, 'longitude' => 33.4336],

            // Red Sea State
            ['name' => 'Port Sudan', 'name_ar' => 'بورتسودان', 'state' => 'Red Sea', 'latitude' => 19.6156, 'longitude' => 37.2162],
            ['name' => 'Suakin', 'name_ar' => 'سواكن', 'state' => 'Red Sea', 'latitude' => 19.1167, 'longitude' => 37.3333],
            ['name' => 'Halaib', 'name_ar' => 'حلايب', 'state' => 'Red Sea', 'latitude' => 22.2167, 'longitude' => 36.6500],
            ['name' => 'Tokar', 'name_ar' => 'طوكر', 'state' => 'Red Sea', 'latitude' => 18.4333, 'longitude' => 37.7333],

            // Kassala State
            ['name' => 'Kassala', 'name_ar' => 'كسلا', 'state' => 'Kassala', 'latitude' => 15.4500, 'longitude' => 36.4000],
            ['name' => 'Halfa Jadida', 'name_ar' => 'حلفا الجديدة', 'state' => 'Kassala', 'latitude' => 15.3333, 'longitude' => 35.6000],
            ['name' => 'Khashm el Girba', 'name_ar' => 'خشم القربة', 'state' => 'Kassala', 'latitude' => 14.9167, 'longitude' => 35.9000],

            // Gedaref State
            ['name' => 'Gedaref', 'name_ar' => 'القضارف', 'state' => 'Gedaref', 'latitude' => 14.0333, 'longitude' => 35.3833],
            ['name' => 'Doka', 'name_ar' => 'الدوكة', 'state' => 'Gedaref', 'latitude' => 14.3667, 'longitude' => 35.7667],
            ['name' => 'Fau', 'name_ar' => 'الفاو', 'state' => 'Gedaref', 'latitude' => 13.6167, 'longitude' => 35.0500],

            // Sennar State
            ['name' => 'Sennar', 'name_ar' => 'سنار', 'state' => 'Sennar', 'latitude' => 13.5500, 'longitude' => 33.6000],
            ['name' => 'Singa', 'name_ar' => 'سنجة', 'state' => 'Sennar', 'latitude' => 13.1500, 'longitude' => 33.9333],

            // Blue Nile State
            ['name' => 'Ed Damazin', 'name_ar' => 'الدمازين', 'state' => 'Blue Nile', 'latitude' => 11.7833, 'longitude' => 34.3500],
            ['name' => 'Kurmuk', 'name_ar' => 'الكرمك', 'state' => 'Blue Nile', 'latitude' => 10.5500, 'longitude' => 34.2667],
            ['name' => 'Roseires', 'name_ar' => 'الروصيرص', 'state' => 'Blue Nile', 'latitude' => 11.8500, 'longitude' => 34.3833],

            // White Nile State
            ['name' => 'Kosti', 'name_ar' => 'كوستي', 'state' => 'White Nile', 'latitude' => 13.1667, 'longitude' => 32.6667],
            ['name' => 'Rabak', 'name_ar' => 'ربك', 'state' => 'White Nile', 'latitude' => 13.1833, 'longitude' => 32.7333],
            ['name' => 'Ed Dueim', 'name_ar' => 'الدويم', 'state' => 'White Nile', 'latitude' => 14.0000, 'longitude' => 32.3167],

            // Gezira State
            ['name' => 'Wad Medani', 'name_ar' => 'ود مدني', 'state' => 'Gezira', 'latitude' => 14.4008, 'longitude' => 33.5196],
            ['name' => 'Managil', 'name_ar' => 'المناقل', 'state' => 'Gezira', 'latitude' => 14.5167, 'longitude' => 33.5000],
            ['name' => 'El Hasahisa', 'name_ar' => 'الحصاحيصا', 'state' => 'Gezira', 'latitude' => 14.9167, 'longitude' => 33.2167],

            // North Kordofan State
            ['name' => 'El Obeid', 'name_ar' => 'الأبيض', 'state' => 'North Kordofan', 'latitude' => 13.1833, 'longitude' => 30.2167],
            ['name' => 'Bara', 'name_ar' => 'بارا', 'state' => 'North Kordofan', 'latitude' => 13.7000, 'longitude' => 30.3667],
            ['name' => 'Sodiri', 'name_ar' => 'سودري', 'state' => 'North Kordofan', 'latitude' => 13.6000, 'longitude' => 30.1333],

            // South Kordofan State
            ['name' => 'Kadugli', 'name_ar' => 'كادقلي', 'state' => 'South Kordofan', 'latitude' => 11.0167, 'longitude' => 29.7167],
            ['name' => 'Dilling', 'name_ar' => 'الدلنج', 'state' => 'South Kordofan', 'latitude' => 12.0500, 'longitude' => 29.6500],
            ['name' => 'Talodi', 'name_ar' => 'تلودي', 'state' => 'South Kordofan', 'latitude' => 10.5333, 'longitude' => 30.4833],

            // West Kordofan State
            ['name' => 'El Fula', 'name_ar' => 'الفولة', 'state' => 'West Kordofan', 'latitude' => 11.7333, 'longitude' => 28.5000],
            ['name' => 'Lagawa', 'name_ar' => 'لقاوة', 'state' => 'West Kordofan', 'latitude' => 11.8000, 'longitude' => 28.9500],

            // North Darfur State
            ['name' => 'El Fasher', 'name_ar' => 'الفاشر', 'state' => 'North Darfur', 'latitude' => 13.6333, 'longitude' => 25.3500],
            ['name' => 'Kutum', 'name_ar' => 'كتم', 'state' => 'North Darfur', 'latitude' => 14.2000, 'longitude' => 24.6667],
            ['name' => 'Mellit', 'name_ar' => 'مليط', 'state' => 'North Darfur', 'latitude' => 14.9500, 'longitude' => 25.8167],
            ['name' => 'Kebkabiya', 'name_ar' => 'كبكابية', 'state' => 'North Darfur', 'latitude' => 13.4333, 'longitude' => 24.1500],

            // South Darfur State
            ['name' => 'Nyala', 'name_ar' => 'نيالا', 'state' => 'South Darfur', 'latitude' => 12.0500, 'longitude' => 24.8833],
            ['name' => 'Ed Daein', 'name_ar' => 'الضعين', 'state' => 'South Darfur', 'latitude' => 11.4667, 'longitude' => 26.1167],
            ['name' => 'Kass', 'name_ar' => 'كاس', 'state' => 'South Darfur', 'latitude' => 12.7500, 'longitude' => 24.1167],
            ['name' => 'Tulus', 'name_ar' => 'تلس', 'state' => 'South Darfur', 'latitude' => 10.9833, 'longitude' => 24.1167],

            // West Darfur State
            ['name' => 'Geneina', 'name_ar' => 'الجنينة', 'state' => 'West Darfur', 'latitude' => 13.4500, 'longitude' => 22.4500],
            ['name' => 'Zalingei', 'name_ar' => 'زالنجي', 'state' => 'Central Darfur', 'latitude' => 12.9167, 'longitude' => 23.4833],
            ['name' => 'Mukjar', 'name_ar' => 'مكجر', 'state' => 'Central Darfur', 'latitude' => 12.2667, 'longitude' => 23.8833],

            // East Darfur State
            ['name' => 'Ed Daein', 'name_ar' => 'الضعين', 'state' => 'East Darfur', 'latitude' => 11.4667, 'longitude' => 26.1167],
            ['name' => 'Adila', 'name_ar' => 'عديلة', 'state' => 'East Darfur', 'latitude' => 11.7833, 'longitude' => 26.4000],

            // Central Darfur State
            ['name' => 'Zalingei', 'name_ar' => 'زالنجي', 'state' => 'Central Darfur', 'latitude' => 12.9167, 'longitude' => 23.4833],
            ['name' => 'Wadi Salih', 'name_ar' => 'وادي صالح', 'state' => 'Central Darfur', 'latitude' => 12.5667, 'longitude' => 22.9333],

            // Additional major cities and towns
            ['name' => 'Abu Hamad', 'name_ar' => 'أبو حمد', 'state' => 'River Nile', 'latitude' => 19.5333, 'longitude' => 33.3167],
            ['name' => 'Karari', 'name_ar' => 'كرري', 'state' => 'Khartoum', 'latitude' => 15.6667, 'longitude' => 32.4500],
            ['name' => 'Jebel Aulia', 'name_ar' => 'جبل أولياء', 'state' => 'Khartoum', 'latitude' => 15.2833, 'longitude' => 32.5000],
            ['name' => 'Mayo', 'name_ar' => 'مايو', 'state' => 'Khartoum', 'latitude' => 15.5500, 'longitude' => 32.5667],
            ['name' => 'Sharg El Nil', 'name_ar' => 'شرق النيل', 'state' => 'Khartoum', 'latitude' => 15.5167, 'longitude' => 32.6667],
            ['name' => 'Umbadda', 'name_ar' => 'أم بدة', 'state' => 'Khartoum', 'latitude' => 15.6500, 'longitude' => 32.4667],
            ['name' => 'Soba', 'name_ar' => 'سوبا', 'state' => 'Khartoum', 'latitude' => 15.4333, 'longitude' => 32.5500],
            ['name' => 'Sabaloka', 'name_ar' => 'سبلوقة', 'state' => 'Khartoum', 'latitude' => 16.1833, 'longitude' => 32.5500],
            ['name' => 'Um Ruwaba', 'name_ar' => 'أم روابة', 'state' => 'North Kordofan', 'latitude' => 12.9000, 'longitude' => 31.2167],
            ['name' => 'En Nahud', 'name_ar' => 'النهود', 'state' => 'West Kordofan', 'latitude' => 12.7000, 'longitude' => 28.4333],
        ];

        foreach ($cities as $city) {
            SudaneseCity::create($city);
        }
    }
}
