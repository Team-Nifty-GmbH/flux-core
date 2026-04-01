<?php

use FluxErp\Livewire\Widgets\MyTargetProgresses;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MyTargetProgresses::class)
        ->assertOk();
});
