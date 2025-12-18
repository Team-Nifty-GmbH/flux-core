<?php

use FluxErp\Livewire\DataTables\SerialNumberRangeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SerialNumberRangeList::class)
        ->assertOk();
});
