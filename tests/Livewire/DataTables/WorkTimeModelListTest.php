<?php

use FluxErp\Livewire\DataTables\WorkTimeModelList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimeModelList::class)
        ->assertOk();
});
