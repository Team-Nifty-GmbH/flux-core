<?php

use FluxErp\Livewire\Widgets\OrdersByTypeChart;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrdersByTypeChart::class)
        ->assertOk();
});
