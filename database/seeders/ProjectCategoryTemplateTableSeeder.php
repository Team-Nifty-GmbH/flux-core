<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Category;
use FluxErp\Models\ProjectCategoryTemplate;
use Illuminate\Database\Seeder;

class ProjectCategoryTemplateTableSeeder extends Seeder
{
    public function run()
    {
        $templates = ProjectCategoryTemplate::factory()->count(5)->create();
        $categories = Category::all();

        foreach ($templates as $template) {
            $template->categories()->sync($categories->random(rand(5, count($categories))));
        }
    }
}
