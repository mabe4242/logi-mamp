<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WmsPartnersSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // suppliers
            $suppliers = [
                [
                    'code' => 'SUP-001',
                    'name' => '山田アパレル卸',
                    'contact_name' => '山田 太郎',
                    'phone' => '06-1234-5678',
                    'email' => 'sales@yamada-apparel.example',
                    'postal_code' => '530-0001',
                    'address1' => '大阪府大阪市北区',
                    'address2' => '梅田1-1-1',
                    'note' => '月末締め翌月末払い',
                ],
                [
                    'code' => 'SUP-002',
                    'name' => '関西雑貨商会',
                    'contact_name' => '佐藤 花子',
                    'phone' => '078-0000-1111',
                    'email' => 'contact@kansai-zakka.example',
                    'postal_code' => '650-0001',
                    'address1' => '兵庫県神戸市中央区',
                    'address2' => '三宮2-2-2',
                    'note' => null,
                ],
            ];

            foreach ($suppliers as $s) {
                Supplier::updateOrCreate(
                    ['code' => $s['code']],
                    $s
                );
            }

            // customers
            $customers = [
                [
                    'code' => 'CUS-001',
                    'name' => '本店（店舗受け取り）',
                    'contact_name' => '店長',
                    'phone' => '06-9999-0000',
                    'email' => null,
                    'postal_code' => '530-0001',
                    'address1' => '大阪府大阪市北区',
                    'address2' => '梅田9-9-9',
                    'shipping_method' => null,
                    'note' => '店頭引き渡し',
                ],
                [
                    'code' => 'CUS-002',
                    'name' => 'EC出荷先（テスト顧客）',
                    'contact_name' => '田中 一郎',
                    'phone' => '090-1111-2222',
                    'email' => 'tanaka@example.com',
                    'postal_code' => '135-0061',
                    'address1' => '東京都江東区',
                    'address2' => '豊洲3-3-3',
                    'shipping_method' => 'ヤマト',
                    'note' => '置き配OK',
                ],
            ];

            foreach ($customers as $c) {
                Customer::updateOrCreate(
                    ['code' => $c['code']],
                    $c
                );
            }
        });
    }
}
