<?php

use FluxErp\Livewire\DataTables\PrinterList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PrinterList::class)
        ->assertOk();
});
