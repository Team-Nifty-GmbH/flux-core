<?php

use FluxErp\Livewire\Widgets\AverageOrderValue;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AverageOrderValue::class)
        ->assertOk();
});
