<?php

use FluxErp\Livewire\DataTables\LogList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LogList::class)
        ->assertOk();
});
