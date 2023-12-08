<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class ProjectTaskTableSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        $users = User::all();

        foreach ($projects as $project) {
            for ($i = 0; $i < 5; $i++) {
                Task::factory()->create([
                    'project_id' => $project->id,
                    'responsible_user_id' => $users->random()->id,
                ]);
            }
        }
    }
}
