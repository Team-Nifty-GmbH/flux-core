<?php

use FluxErp\Livewire\DataTables\ProductBundleProductList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductBundleProductList::class)
        ->assertOk();
});
