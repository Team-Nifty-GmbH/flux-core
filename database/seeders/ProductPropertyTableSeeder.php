<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\ProductProperty;
use Illuminate\Database\Seeder;

class ProductPropertyTableSeeder extends Seeder
{
    public function run(): void
    {
        ProductProperty::factory()->count(10)->create();
    }
}
