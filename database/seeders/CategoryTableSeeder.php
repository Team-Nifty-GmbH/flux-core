<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Category;
use FluxErp\Models\Task;
use Illuminate\Database\Seeder;

class CategoryTableSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::factory()->count(10)->create(['model_type' => Task::class]);

        foreach ($categories as $category) {
            Category::factory()->count(3)->create([
                'parent_id' => $category->id,
                'model_type' => Task::class,
            ]);
        }
    }
}
