<?php

use FluxErp\Livewire\DataTables\OrderPositionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderPositionList::class)
        ->assertStatus(200);
});
