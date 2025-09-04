<?php

use FluxErp\Livewire\Order\Projects;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Projects::class)
        ->assertOk();
});
