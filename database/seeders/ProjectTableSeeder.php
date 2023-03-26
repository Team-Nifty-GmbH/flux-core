<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Project;
use FluxErp\Models\ProjectCategoryTemplate;
use Illuminate\Database\Seeder;

class ProjectTableSeeder extends Seeder
{
    public function run()
    {
        $templates = ProjectCategoryTemplate::all();
        for ($i = 0; $i < 10; $i++) {
            $template = $templates->random();
            $project = Project::factory()->create([
                'project_category_template_id' => $template->id,
            ]);

            $categories = $template->categories;

            if (count($categories)) {
                $categoriesSync = $categories->random(rand(1, count($categories)));
                $project->categories()->sync($categoriesSync);
            }
        }
    }
}
