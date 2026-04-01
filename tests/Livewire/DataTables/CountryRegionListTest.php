<?php

use FluxErp\Livewire\DataTables\CountryRegionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CountryRegionList::class)
        ->assertOk();
});
