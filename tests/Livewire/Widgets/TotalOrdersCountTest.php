<?php

use FluxErp\Livewire\Widgets\TotalOrdersCount;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TotalOrdersCount::class)
        ->assertOk();
});
