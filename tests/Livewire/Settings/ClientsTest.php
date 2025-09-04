<?php

use FluxErp\Livewire\Settings\Clients;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Clients::class)
        ->assertOk();
});
