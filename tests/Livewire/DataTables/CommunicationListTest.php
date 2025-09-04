<?php

use FluxErp\Livewire\DataTables\CommunicationList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CommunicationList::class)
        ->assertOk();
});
