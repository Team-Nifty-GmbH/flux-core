<?php

use FluxErp\Livewire\DataTables\ProductOptionGroupList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductOptionGroupList::class)
        ->assertOk();
});
