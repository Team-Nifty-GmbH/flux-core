<?php

use FluxErp\Livewire\Widgets\TopProductsByRevenue;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TopProductsByRevenue::class)
        ->assertOk();
});
