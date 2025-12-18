<?php

use FluxErp\Livewire\Order\Texts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Texts::class)
        ->assertOk();
});
