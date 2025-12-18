<?php

use FluxErp\Livewire\DataTables\WorkTimeTypeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimeTypeList::class)
        ->assertOk();
});
