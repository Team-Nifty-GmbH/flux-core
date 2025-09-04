<?php

use FluxErp\Livewire\DataTables\PriceListList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PriceListList::class)
        ->assertStatus(200);
});
