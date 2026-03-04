<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use Illuminate\Database\Seeder;

class PriceTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 30; $i++) {
            Price::factory()->create([
                'price_list_id' => PriceList::all(['id'])->random()->id,
                'product_id' => Product::all(['id'])->random()->id,
            ]);
        }
    }
}
