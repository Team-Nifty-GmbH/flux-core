<?php

use FluxErp\Livewire\DataTables\RoleList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RoleList::class)
        ->assertOk();
});
