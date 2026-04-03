<?php

use FluxErp\Livewire\Widgets\OrderMediaChart;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderMediaChart::class)
        ->assertOk();
});
