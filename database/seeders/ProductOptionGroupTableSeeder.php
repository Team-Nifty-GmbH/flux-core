<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\ProductOptionGroup;
use Illuminate\Database\Seeder;

class ProductOptionGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductOptionGroup::factory()->count(10)->create();
    }
}
