<?php

use FluxErp\Livewire\DataTables\TenantList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TenantList::class)
        ->assertOk();
});
