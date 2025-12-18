<?php

use FluxErp\Livewire\Product\BundleList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(BundleList::class)
        ->assertOk();
});
