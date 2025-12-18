<?php

use FluxErp\Livewire\Order\ReplicateOrderPositionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ReplicateOrderPositionList::class)
        ->assertOk();
});
