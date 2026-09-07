<?php

use FluxErp\Livewire\Widgets\RecurringCostsForecast;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RecurringCostsForecast::class)
        ->assertOk();
});
