<?php

use FluxErp\Livewire\Widgets\ActiveLeadsByClosingProbability;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ActiveLeadsByClosingProbability::class)
        ->assertOk();
});
