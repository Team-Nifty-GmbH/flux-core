<?php

use FluxErp\Livewire\Settings\Currencies;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Currencies::class)
        ->assertOk();
});
