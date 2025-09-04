<?php

use FluxErp\Livewire\DataTables\LeadList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LeadList::class)
        ->assertOk();
});
