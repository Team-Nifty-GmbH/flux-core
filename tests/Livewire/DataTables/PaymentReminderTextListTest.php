<?php

use FluxErp\Livewire\DataTables\PaymentReminderTextList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentReminderTextList::class)
        ->assertOk();
});
