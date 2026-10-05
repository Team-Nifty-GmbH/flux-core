<?php

use FluxErp\Livewire\DataTables\ResourceBookingList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ResourceBookingList::class)
        ->assertOk();
});
