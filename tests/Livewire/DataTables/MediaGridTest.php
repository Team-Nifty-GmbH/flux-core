<?php

use FluxErp\Livewire\Product\MediaGrid;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MediaGrid::class)
        ->assertOk();
});
