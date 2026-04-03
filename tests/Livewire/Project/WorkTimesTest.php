<?php

use FluxErp\Livewire\Project\WorkTimes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $project = app(FluxErp\Models\Project::class)->create(['tenant_id' => $this->dbTenant->getKey(), 'name' => 'Test']);

    Livewire::test(WorkTimes::class, ['projectId' => $project->getKey()])
        ->assertOk();
});
