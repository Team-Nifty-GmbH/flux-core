<?php

use FluxErp\Livewire\DataTables\ProjectList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProjectList::class)
        ->assertStatus(200);
});
