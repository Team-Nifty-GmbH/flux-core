<?php

use FluxErp\Livewire\Lead\General;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(General::class)
        ->assertOk();
});
