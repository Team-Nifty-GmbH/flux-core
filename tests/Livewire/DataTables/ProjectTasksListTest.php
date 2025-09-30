<?php

use FluxErp\Livewire\DataTables\TaskList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TaskList::class)
        ->assertOk();
});
