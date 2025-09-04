<?php

use FluxErp\Livewire\DataTables\ProductCrossSellingList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductCrossSellingList::class)
        ->assertStatus(200);
});
