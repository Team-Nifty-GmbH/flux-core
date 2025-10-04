<?php

use FluxErp\Livewire\DataTables\Settings\DiscountGroupList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(DiscountGroupList::class)
        ->assertOk();
});
