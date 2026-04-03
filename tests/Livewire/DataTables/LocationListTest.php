<?php

use FluxErp\Livewire\DataTables\LocationList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LocationList::class)
        ->assertOk();
});
