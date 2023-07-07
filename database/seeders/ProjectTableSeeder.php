<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Category;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectTask;
use Illuminate\Database\Seeder;

class ProjectTableSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::query()
            ->where('model_type', ProjectTask::class)
            ->get();
        for ($i = 0; $i < 10; $i++) {
            $category = $categories->random();
            $project = Project::factory()->create([
                'category_id' => $category->id,
            ]);

            $categories = $category->children;

            if (count($categories)) {
                $categoriesSync = $categories->random(rand(1, count($categories)));
                $project->categories()->sync($categoriesSync);
            }
        }
    }
}
