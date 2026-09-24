<?php

use FluxErp\Livewire\Resource\ResourceList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ResourceList::class)
        ->assertOk();
});
