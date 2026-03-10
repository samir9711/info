<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        $currencies = [
            ['code'=>'USD','name'=>['en'=>'United States Dollar','ar'=>'الدولار الأمريكي'],'symbol'=>'$'],
            ['code'=>'EUR','name'=>['en'=>'Euro','ar'=>'اليورو'],'symbol'=>'€'],
            ['code'=>'GBP','name'=>['en'=>'British Pound Sterling','ar'=>'الجنيه الإسترليني'],'symbol'=>'£'],
            ['code'=>'JPY','name'=>['en'=>'Japanese Yen','ar'=>'الين الياباني'],'symbol'=>'¥'],
            ['code'=>'CNY','name'=>['en'=>'Chinese Yuan','ar'=>'اليوان الصيني'],'symbol'=>'¥'],
            ['code'=>'CAD','name'=>['en'=>'Canadian Dollar','ar'=>'الدولار الكندي'],'symbol'=>'$'],
            ['code'=>'AUD','name'=>['en'=>'Australian Dollar','ar'=>'الدولار الأسترالي'],'symbol'=>'$'],
            ['code'=>'NZD','name'=>['en'=>'New Zealand Dollar','ar'=>'الدولار النيوزيلندي'],'symbol'=>'$'],
            ['code'=>'CHF','name'=>['en'=>'Swiss Franc','ar'=>'الفرنك السويسري'],'symbol'=>'CHF'],
            ['code'=>'SEK','name'=>['en'=>'Swedish Krona','ar'=>'الكرونة السويدية'],'symbol'=>'kr'],
            ['code'=>'NOK','name'=>['en'=>'Norwegian Krone','ar'=>'الكرونة النرويجية'],'symbol'=>'kr'],
            ['code'=>'DKK','name'=>['en'=>'Danish Krone','ar'=>'الكرونة الدنماركية'],'symbol'=>'kr'],
            ['code'=>'INR','name'=>['en'=>'Indian Rupee','ar'=>'الروبية الهندية'],'symbol'=>'₹'],
            ['code'=>'PKR','name'=>['en'=>'Pakistani Rupee','ar'=>'الروبية الباكستانية'],'symbol'=>'₨'],
            ['code'=>'TRY','name'=>['en'=>'Turkish Lira','ar'=>'الليرة التركية'],'symbol'=>'₺'],
            ['code'=>'RUB','name'=>['en'=>'Russian Ruble','ar'=>'الروبل الروسي'],'symbol'=>'₽'],
            ['code'=>'BRL','name'=>['en'=>'Brazilian Real','ar'=>'الريال البرازيلي'],'symbol'=>'R$'],
            ['code'=>'ZAR','name'=>['en'=>'South African Rand','ar'=>'الراند الجنوب أفريقي'],'symbol'=>'R'],
            ['code'=>'MXN','name'=>['en'=>'Mexican Peso','ar'=>'البيزو المكسيكي'],'symbol'=>'$'],
            ['code'=>'SGD','name'=>['en'=>'Singapore Dollar','ar'=>'الدولار السنغافوري'],'symbol'=>'$'],
            ['code'=>'HKD','name'=>['en'=>'Hong Kong Dollar','ar'=>'الدولار الهونغ كونغي'],'symbol'=>'$'],
            ['code'=>'KRW','name'=>['en'=>'South Korean Won','ar'=>'الوون الكوري الجنوبي'],'symbol'=>'₩'],
            ['code'=>'IDR','name'=>['en'=>'Indonesian Rupiah','ar'=>'الروبية الإندونيسية'],'symbol'=>'Rp'],
            ['code'=>'THB','name'=>['en'=>'Thai Baht','ar'=>'البات التايلاندي'],'symbol'=>'฿'],
            ['code'=>'AED','name'=>['en'=>'UAE Dirham','ar'=>'الدرهم الإماراتي'],'symbol'=>'د.إ'],
            ['code'=>'SAR','name'=>['en'=>'Saudi Riyal','ar'=>'الريال السعودي'],'symbol'=>'﷼'],
            ['code'=>'QAR','name'=>['en'=>'Qatari Riyal','ar'=>'الريال القطري'],'symbol'=>'﷼'],
            ['code'=>'OMR','name'=>['en'=>'Omani Rial','ar'=>'الريال العماني'],'symbol'=>'ر.ع'],
            ['code'=>'BHD','name'=>['en'=>'Bahraini Dinar','ar'=>'الدينار البحريني'],'symbol'=>'.د.ب'],
            ['code'=>'KWD','name'=>['en'=>'Kuwaiti Dinar','ar'=>'الدينار الكويتي'],'symbol'=>'د.ك'],
            ['code'=>'EGP','name'=>['en'=>'Egyptian Pound','ar'=>'الجنيه المصري'],'symbol'=>'£'],
        ];

        $rows = [];
        foreach ($currencies as $c) {
            $rows[] = [
                'code'       => $c['code'],
                'name'       => json_encode($c['name'], JSON_UNESCAPED_UNICODE),
                'symbol'     => $c['symbol'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // إدراج الدفعة
        DB::table('currencies')->insert($rows);
    }
}
