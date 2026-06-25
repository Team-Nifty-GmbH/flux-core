<?php

use FluxErp\Livewire\DataTables\ResourceBookingList;
use Livewire\Livewire;

test('resource booking list renders', function (): void {
    Livewire::test(ResourceBookingList::class)
        ->assertOk();
});
