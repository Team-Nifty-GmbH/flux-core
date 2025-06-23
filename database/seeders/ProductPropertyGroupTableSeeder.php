<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\ProductPropertyGroup;
use Illuminate\Database\Seeder;

class ProductPropertyGroupTableSeeder extends Seeder
{
    public function run(): void
    {
        ProductPropertyGroup::factory()
            ->count(5)
            ->create();
    }
}
