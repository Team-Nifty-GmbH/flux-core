<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\DiscountGroup;
use Illuminate\Database\Seeder;

class DiscountGroupTableSeeder extends Seeder
{
    public function run(): void
    {
        DiscountGroup::factory()->count(rand(5, 7))->create();
    }
}
