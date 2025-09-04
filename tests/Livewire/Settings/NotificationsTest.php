<?php

use FluxErp\Livewire\Settings\Notifications;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Notifications::class)
        ->assertOk();
});
