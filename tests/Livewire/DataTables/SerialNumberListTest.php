<?php

use FluxErp\Livewire\DataTables\SerialNumberList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SerialNumberList::class)
        ->assertOk();
});
