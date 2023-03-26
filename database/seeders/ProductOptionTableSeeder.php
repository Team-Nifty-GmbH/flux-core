<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Database\Seeder;

class ProductOptionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groups = ProductOptionGroup::all();
        foreach ($groups as $group) {
            ProductOption::factory()->count(3)->create([
                'product_option_group_id' => $group->id,
            ]);
        }
    }
}
