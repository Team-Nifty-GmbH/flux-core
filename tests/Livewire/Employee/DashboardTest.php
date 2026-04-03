<?php

use FluxErp\Livewire\Employee\Dashboard;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Dashboard::class)
        ->assertOk();
});
