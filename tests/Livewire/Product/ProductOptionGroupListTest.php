<?php

use FluxErp\Livewire\Product\ProductOptionGroupList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductOptionGroupList::class)
        ->assertOk();
});
