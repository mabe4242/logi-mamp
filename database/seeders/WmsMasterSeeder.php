<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WmsMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            /**
             * 1) Locations（棚・ロケ）
             * 小売店想定：バックヤードに「棚番」があるイメージ
             */
            $locations = [
                ['code' => 'A-01', 'name' => '棚A-01（定番）', 'note' => null],
                ['code' => 'A-02', 'name' => '棚A-02（定番）', 'note' => null],
                ['code' => 'A-03', 'name' => '棚A-03（定番）', 'note' => null],
                ['code' => 'B-01', 'name' => '棚B-01（アパレル）', 'note' => null],
                ['code' => 'B-02', 'name' => '棚B-02（雑貨）', 'note' => null],
                ['code' => 'C-01', 'name' => '棚C-01（小物）', 'note' => null],
                ['code' => 'NG-01', 'name' => '不良品置き場', 'note' => '破損・返品など一時保管'],
            ];

            $locationModels = [];
            foreach ($locations as $loc) {
                $locationModels[$loc['code']] = Location::updateOrCreate(
                    ['code' => $loc['code']],
                    ['name' => $loc['name'], 'note' => $loc['note']]
                );
            }

            /**
             * 2) Products（商品マスタ）
             * 例：小売店でありがちなSKU
             */
            $products = [
                [
                    'sku' => 'TSHIRT-BLK-M',
                    'barcode' => '4900000000011',
                    'name' => 'Tシャツ（黒）M',
                    'unit' => 'piece',
                    'image_path' => null,
                    'note' => '定番商品',
                ],
                [
                    'sku' => 'TSHIRT-WHT-M',
                    'barcode' => '4900000000028',
                    'name' => 'Tシャツ（白）M',
                    'unit' => 'piece',
                    'image_path' => null,
                    'note' => '定番商品',
                ],
                [
                    'sku' => 'CAP-NVY-F',
                    'barcode' => '4900000000035',
                    'name' => 'キャップ（ネイビー）フリー',
                    'unit' => 'piece',
                    'image_path' => null,
                    'note' => null,
                ],
                [
                    'sku' => 'SOCKS-WHT',
                    'barcode' => '4900000000042',
                    'name' => '靴下（白）',
                    'unit' => 'pair',
                    'image_path' => null,
                    'note' => null,
                ],
                [
                    'sku' => 'TOTE-BAG',
                    'barcode' => '4900000000059',
                    'name' => 'トートバッグ',
                    'unit' => 'piece',
                    'image_path' => null,
                    'note' => null,
                ],
            ];

            $productModels = [];
            foreach ($products as $p) {
                $productModels[$p['sku']] = Product::updateOrCreate(
                    ['sku' => $p['sku']],
                    [
                        'barcode' => $p['barcode'],
                        'name' => $p['name'],
                        'unit' => $p['unit'],
                        'image_path' => $p['image_path'],
                        'note' => $p['note'],
                    ]
                );
            }

            /**
             * 3) Stocks（在庫）
             * stocks は (product_id, location_id) UNIQUE のため、
             * 1商品を複数棚に分けるパターンも少し入れて「現場感」を出す
             */
            $seedStocks = [
                // Tシャツ黒M：定番棚 + 予備棚
                ['sku' => 'TSHIRT-BLK-M', 'loc' => 'A-01', 'on_hand' => 80, 'reserved' => 5],
                ['sku' => 'TSHIRT-BLK-M', 'loc' => 'A-02', 'on_hand' => 20, 'reserved' => 0],

                // Tシャツ白M
                ['sku' => 'TSHIRT-WHT-M', 'loc' => 'A-01', 'on_hand' => 60, 'reserved' => 2],

                // キャップ
                ['sku' => 'CAP-NVY-F', 'loc' => 'B-01', 'on_hand' => 35, 'reserved' => 0],

                // 靴下
                ['sku' => 'SOCKS-WHT', 'loc' => 'C-01', 'on_hand' => 120, 'reserved' => 10],

                // トートバッグ
                ['sku' => 'TOTE-BAG', 'loc' => 'B-02', 'on_hand' => 15, 'reserved' => 0],
            ];

            foreach ($seedStocks as $s) {
                $product = $productModels[$s['sku']];
                $location = $locationModels[$s['loc']];

                Stock::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'location_id' => $location->id,
                    ],
                    [
                        'on_hand_qty' => $s['on_hand'],
                        'reserved_qty' => $s['reserved'],
                    ]
                );
            }
        });
    }
}
