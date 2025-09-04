<?php

use FluxErp\Livewire\Widgets\TotalRevenue;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TotalRevenue::class)
        ->assertOk();
});
