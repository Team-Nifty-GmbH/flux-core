<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use Illuminate\Database\Seeder;

class PriceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 30; $i++) {
            Price::factory()->create([
                'product_id' => Product::all()->random()->id,
                'price_list_id' => PriceList::all()->random()->id,
            ]);
        }
    }
}
