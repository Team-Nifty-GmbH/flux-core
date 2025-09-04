<?php

use FluxErp\Livewire\Settings\Tokens;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tokens::class)
        ->assertOk();
});
