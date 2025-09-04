<?php

use FluxErp\Livewire\Settings\Industries;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Industries::class)
        ->assertOk();
});
