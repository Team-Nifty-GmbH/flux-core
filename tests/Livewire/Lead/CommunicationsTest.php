<?php

use FluxErp\Livewire\Lead\Communications;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Communications::class)
        ->assertOk();
});
