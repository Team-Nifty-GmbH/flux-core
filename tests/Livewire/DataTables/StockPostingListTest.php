<?php

use FluxErp\Livewire\DataTables\StockPostingList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(StockPostingList::class)
        ->assertOk();
});
