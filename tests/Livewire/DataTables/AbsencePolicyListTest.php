<?php

use FluxErp\Livewire\DataTables\AbsencePolicyList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AbsencePolicyList::class)
        ->assertOk();
});
