<?php

use FluxErp\Livewire\Widgets\Revenue;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Revenue::class)
        ->assertOk();
});
