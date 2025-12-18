<?php

use FluxErp\Livewire\DataTables\IndustryList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(IndustryList::class)
        ->assertOk();
});
