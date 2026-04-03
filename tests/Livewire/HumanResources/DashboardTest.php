<?php

use FluxErp\Livewire\HumanResources\Dashboard;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Dashboard::class)
        ->assertOk();
});
