<?php

use FluxErp\Livewire\Widgets\MyLeadWonLostRatio;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MyLeadWonLostRatio::class)
        ->assertOk();
});
