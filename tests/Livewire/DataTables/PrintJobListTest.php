<?php

use FluxErp\Livewire\DataTables\PrintJobList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PrintJobList::class)
        ->assertOk();
});
