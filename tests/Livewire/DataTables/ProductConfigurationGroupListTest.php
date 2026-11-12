<?php

use FluxErp\Livewire\DataTables\ProductConfigurationGroupList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductConfigurationGroupList::class)
        ->assertOk();
});
