<?php

use FluxErp\Livewire\Accounting\PaymentReminderRun;
use Livewire\Livewire;

test('payment reminder run renders', function (): void {
    Livewire::test(PaymentReminderRun::class)
        ->assertOk();
});
