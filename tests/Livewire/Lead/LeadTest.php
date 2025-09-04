<?php

use FluxErp\Livewire\Lead\Lead;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Lead::class)
        ->assertOk();
});
