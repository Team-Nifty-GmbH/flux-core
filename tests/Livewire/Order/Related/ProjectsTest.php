<?php

use FluxErp\Livewire\Order\Related\Projects;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Projects::class)
        ->assertOk();
});
