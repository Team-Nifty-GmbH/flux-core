<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TaskTableSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $projects = Project::all(['id']);
        $users = User::all(['id']);

        foreach ($projects as $project) {
            for ($i = 0; $i < 5; $i++) {
                $task = Task::factory()->create([
                    'uuid' => Str::uuid(),
                    'project_id' => $project->id,
                    'responsible_user_id' => $users->random()->id,
                ]);

                $task->users()->attach($users->random()->id);
            }

            $project->calculateProgress();
        }
    }
}
