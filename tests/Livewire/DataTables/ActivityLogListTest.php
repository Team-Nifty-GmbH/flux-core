<?php

use FluxErp\Livewire\DataTables\ActivityLogList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ActivityLogList::class)
        ->assertOk();
});
