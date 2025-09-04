<?php

use FluxErp\Livewire\Settings\Permissions;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Permissions::class)
        ->assertOk();
});
