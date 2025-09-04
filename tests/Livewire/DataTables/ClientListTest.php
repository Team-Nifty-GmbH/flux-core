<?php

use FluxErp\Livewire\DataTables\ClientList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ClientList::class)
        ->assertStatus(200);
});
