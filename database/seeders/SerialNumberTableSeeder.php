<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use Illuminate\Database\Seeder;

class SerialNumberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $models = [
            Address::class,
        ];

        foreach ($models as $model) {
            $records = $model::all();

            foreach ($records as $record) {
                for ($i = 0; $i < rand(0, 10); $i++) {
                    SerialNumber::factory()->create([
                        'address_id' => $record->id,
                        'product_id' => rand(0, 1) ? Product::query()->inRandomOrder()->first()->id : null,
                        'order_position_id' => rand(0, 1) ? OrderPosition::query()->inRandomOrder()->first()->id : null,
                    ]);
                }
            }
        }
    }
}
