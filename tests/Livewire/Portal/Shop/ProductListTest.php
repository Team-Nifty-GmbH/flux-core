<?php

use FluxErp\Livewire\Portal\Shop\ProductList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductList::class)
        ->assertOk();
});
