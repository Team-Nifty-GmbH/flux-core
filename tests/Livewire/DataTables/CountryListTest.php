<?php

use FluxErp\Livewire\DataTables\CountryList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CountryList::class)
        ->assertStatus(200);
});
