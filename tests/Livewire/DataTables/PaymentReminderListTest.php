<?php

use FluxErp\Livewire\DataTables\PaymentReminderList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentReminderList::class)
        ->assertOk();
});
