<?php

use FluxErp\Livewire\Settings\Tenants;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tenants::class)
        ->assertOk();
});
