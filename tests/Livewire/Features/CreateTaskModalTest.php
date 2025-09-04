<?php

use FluxErp\Livewire\Features\CreateTaskModal;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CreateTaskModal::class)
        ->assertOk();
});
