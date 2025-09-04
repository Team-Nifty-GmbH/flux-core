<?php

use FluxErp\Livewire\Settings\PaymentTypes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentTypes::class)
        ->assertOk();
});
