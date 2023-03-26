<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\ProductOptionGroup;
use Illuminate\Database\Seeder;

class ProductOptionGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProductOptionGroup::factory()->count(10)->create();
    }
}
