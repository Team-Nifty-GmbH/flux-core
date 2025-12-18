<?php

use FluxErp\Livewire\DataTables\LeadStateList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LeadStateList::class)
        ->assertOk();
});
