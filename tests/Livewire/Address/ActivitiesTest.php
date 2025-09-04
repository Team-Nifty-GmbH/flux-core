<?php

use FluxErp\Livewire\Address\Activities;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Activities::class)
        ->assertOk();
});
