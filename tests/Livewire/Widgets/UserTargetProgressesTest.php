<?php

use FluxErp\Livewire\Widgets\UserTargetProgresses;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(UserTargetProgresses::class)
        ->assertOk();
});
