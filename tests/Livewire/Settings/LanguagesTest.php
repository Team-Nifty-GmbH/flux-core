<?php

use FluxErp\Livewire\Settings\Languages;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Languages::class)
        ->assertOk();
});
