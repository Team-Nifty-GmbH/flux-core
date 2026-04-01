<?php

use FluxErp\Livewire\DataTables\LeadLossReasonList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LeadLossReasonList::class)
        ->assertOk();
});
