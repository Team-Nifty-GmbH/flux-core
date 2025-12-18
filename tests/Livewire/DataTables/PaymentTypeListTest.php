<?php

use FluxErp\Livewire\DataTables\PaymentTypeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentTypeList::class)
        ->assertOk();
});
