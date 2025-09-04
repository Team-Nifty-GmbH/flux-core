<?php

use FluxErp\Livewire\Settings\Plugins;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Plugins::class)
        ->assertOk();
});
