<?php

use FluxErp\Livewire\DataTables\VatRateList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(VatRateList::class)
        ->assertOk();
});
