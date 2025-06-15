<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Tag;
use Illuminate\Database\Seeder;

class TagTableSeeder extends Seeder
{
    public function run(): void
    {
        Tag::factory()
            ->count(rand(10, 20))
            ->create();
    }
}
