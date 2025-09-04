<?php

use FluxErp\Livewire\DataTables\DiscountGroupList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(DiscountGroupList::class)
        ->assertOk();
});
