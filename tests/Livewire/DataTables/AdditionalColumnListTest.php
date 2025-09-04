<?php

use FluxErp\Livewire\DataTables\AdditionalColumnList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AdditionalColumnList::class)
        ->assertOk();
});
