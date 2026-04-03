<?php

use FluxErp\Livewire\Widgets\RecurringRevenueForecast;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RecurringRevenueForecast::class)
        ->assertOk();
});
