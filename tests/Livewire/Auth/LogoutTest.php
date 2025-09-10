<?php

use FluxErp\Livewire\Auth\Logout;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Logout::class)
        ->assertOk();
});
