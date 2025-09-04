<?php

use FluxErp\Livewire\Project\ProjectTaskList;
use FluxErp\Models\Project;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $this->project = Project::factory()->create([
        'client_id' => $client->id,
    ]);
    Livewire::test(ProjectTaskList::class, ['projectId' => $this->project->id])
        ->assertOk();
});
