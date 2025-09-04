<?php

use FluxErp\Livewire\DataTables\UserList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(UserList::class)
        ->assertStatus(200);
});
