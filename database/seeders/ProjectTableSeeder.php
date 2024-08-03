<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Project;
use Illuminate\Database\Seeder;

class ProjectTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all(['id']);

        Project::factory()->count(10)->create([
            'client_id' => $clients->random()->id,
        ]);
    }
}
