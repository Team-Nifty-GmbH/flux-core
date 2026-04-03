<?php

use FluxErp\Livewire\DataTables\VacationCarryOverRuleList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(VacationCarryOverRuleList::class)
        ->assertOk();
});
