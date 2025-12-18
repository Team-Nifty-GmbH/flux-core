<?php

use FluxErp\Livewire\Settings\PaymentReminderTexts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentReminderTexts::class)
        ->assertOk();
});
