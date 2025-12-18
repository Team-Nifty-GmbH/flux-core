<?php

use FluxErp\Livewire\DataTables\PaymentRunList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentRunList::class)
        ->assertOk();
});
