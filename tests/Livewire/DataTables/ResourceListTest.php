<?php

use FluxErp\Livewire\DataTables\ResourceList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ResourceList::class)
        ->assertOk();
});
