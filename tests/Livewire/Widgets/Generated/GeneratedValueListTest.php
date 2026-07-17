<?php

use FluxErp\Livewire\Widgets\Generated\GeneratedValueList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(GeneratedValueList::class)
        ->assertOk();
});
