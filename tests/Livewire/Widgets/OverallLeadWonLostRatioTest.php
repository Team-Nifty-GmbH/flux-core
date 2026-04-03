<?php

use FluxErp\Livewire\Widgets\OverallLeadWonLostRatio;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OverallLeadWonLostRatio::class)
        ->assertOk();
});
