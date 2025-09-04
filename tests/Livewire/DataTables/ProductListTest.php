<?php

use FluxErp\Livewire\DataTables\ProductList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductList::class)
        ->assertOk();
});
