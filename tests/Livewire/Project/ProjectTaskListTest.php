<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Project\ProjectTaskList;
use FluxErp\Models\Client;
use FluxErp\Models\Project;
use Livewire\Livewire;

beforeEach(function (): void {
    $client = Client::factory()->create([
        'is_default' => true,
    ]);
    $this->project = Project::factory()->create([
        'client_id' => $client->id,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(ProjectTaskList::class, ['projectId' => $this->project->id])
        ->assertStatus(200);
});
