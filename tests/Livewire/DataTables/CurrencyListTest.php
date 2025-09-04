<?php

use FluxErp\Livewire\DataTables\CurrencyList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CurrencyList::class)
        ->assertStatus(200);
});
