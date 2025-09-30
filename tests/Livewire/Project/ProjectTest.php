<?php

use FluxErp\Livewire\Project\Project as ProjectView;
use FluxErp\Models\Project;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->project = Project::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(ProjectView::class, ['id' => $this->project->id])
        ->assertOk();
});

test('switch tabs', function (): void {
    Livewire::test(ProjectView::class, ['id' => $this->project->id])->cycleTabs();
});
