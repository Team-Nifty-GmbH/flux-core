<?php

use FluxErp\Livewire\Widgets\RevenuePurchasesProfitChart;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RevenuePurchasesProfitChart::class)
        ->assertOk();
});
