<?php

use FluxErp\Livewire\DataTables\OrderTypeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderTypeList::class)
        ->assertStatus(200);
});
