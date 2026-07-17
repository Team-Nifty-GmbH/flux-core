<?php

use FluxErp\Livewire\Project\WorkTimes;
use FluxErp\Models\Project;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $project = app(Project::class)->create(['tenant_id' => $this->dbTenant->getKey(), 'name' => 'Test']);

    Livewire::test(WorkTimes::class, ['projectId' => $project->getKey()])
        ->assertOk();
});

test('has selectable rows and create orders action', function (): void {
    $project = app(Project::class)->create(['tenant_id' => $this->dbTenant->getKey(), 'name' => 'Test']);

    $component = Livewire::test(WorkTimes::class, ['projectId' => $project->getKey()])
        ->assertOk();

    expect($component->instance())
        ->isSelectable->toBeTrue()
        ->and($component->instance())->toHaveProperty('createOrdersFromWorkTimes');
});
