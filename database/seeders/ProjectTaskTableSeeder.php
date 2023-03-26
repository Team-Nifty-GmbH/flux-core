<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectTask;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class ProjectTaskTableSeeder extends Seeder
{
    public function run()
    {
        $projects = Project::all();
        $addresses = Address::all();
        $users = User::all();

        foreach ($projects as $project) {
            for ($i = 0; $i < 10; $i++) {
                $categories = $project->categoryTemplate->categories;
                $projectTask = ProjectTask::factory()->create([
                    'project_id' => $project->id,
                    'address_id' => $addresses->random()->id,
                    'user_id' => $users->random()->id,
                    'order_position_id' => null,
                ]);

                if (count($categories)) {
                    $projectTask->category()->attach($categories?->random()?->id ?: $categories[0]->id);
                }
            }
        }
    }
}
