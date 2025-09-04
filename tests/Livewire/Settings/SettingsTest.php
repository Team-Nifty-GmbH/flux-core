<?php

use FluxErp\Livewire\Settings\Settings;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Settings::class)
        ->assertOk();
});
