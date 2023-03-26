<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\ProductProperty;
use Illuminate\Database\Seeder;

class ProductPropertyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProductProperty::factory()->count(10)->create();
    }
}
