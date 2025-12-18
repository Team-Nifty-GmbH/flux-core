<?php

use FluxErp\Livewire\DataTables\DiscountList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(DiscountList::class)
        ->assertOk();
});
