<?php

use FluxErp\Livewire\Widgets\UserLeadWonLostRatio;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(UserLeadWonLostRatio::class)
        ->assertOk();
});
