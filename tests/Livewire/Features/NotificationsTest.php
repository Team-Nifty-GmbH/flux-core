<?php

use FluxErp\Livewire\Features\Notifications;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Notifications::class)
        ->assertOk();
});
