<?php

use FluxErp\Livewire\Widgets\Generated\GeneratedLineChart;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(GeneratedLineChart::class)
        ->assertOk();
});
