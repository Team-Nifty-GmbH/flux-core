<?php

use FluxErp\Livewire\Lead\Tasks;
use FluxErp\Models\Lead;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $lead = Lead::factory()->create();

    Livewire::test(Tasks::class, ['modelId' => $lead->getKey()])
        ->assertOk();
});
