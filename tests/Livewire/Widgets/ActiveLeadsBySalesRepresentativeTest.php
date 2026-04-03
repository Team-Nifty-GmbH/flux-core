<?php

use FluxErp\Livewire\Widgets\ActiveLeadsBySalesRepresentative;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ActiveLeadsBySalesRepresentative::class)
        ->assertOk();
});
