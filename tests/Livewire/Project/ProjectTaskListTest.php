<?php

use FluxErp\Livewire\Project\ProjectTaskList;
use FluxErp\Models\Project;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $project = Project::factory()->create([
        'client_id' => $this->dbClient->id,
    ]);

    Livewire::test(ProjectTaskList::class, ['projectId' => $project->id])
        ->assertOk();
});
