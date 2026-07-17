<?php

use FluxErp\Livewire\Widgets\Generated\GeneratedBarChart;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(GeneratedBarChart::class)
        ->assertOk();
});
