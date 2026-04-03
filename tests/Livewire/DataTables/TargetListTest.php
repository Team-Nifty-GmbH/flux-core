<?php

use FluxErp\Livewire\DataTables\TargetList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TargetList::class)
        ->assertOk();
});
