<?php

use FluxErp\Livewire\Widgets\ExpectedRevenueByLeadState;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ExpectedRevenueByLeadState::class)
        ->assertOk();
});
