<?php

use FluxErp\Livewire\DataTables\FailedJobList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(FailedJobList::class)
        ->assertOk();
});
