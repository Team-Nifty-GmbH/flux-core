<?php

use FluxErp\Livewire\Portal\Service;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Service::class)
        ->assertOk();
});
