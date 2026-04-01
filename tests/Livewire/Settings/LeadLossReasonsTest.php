<?php

use FluxErp\Livewire\Settings\LeadLossReasons;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LeadLossReasons::class)
        ->assertOk();
});
