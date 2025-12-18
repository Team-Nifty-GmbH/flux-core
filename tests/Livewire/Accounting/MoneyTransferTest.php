<?php

use FluxErp\Livewire\Accounting\MoneyTransfer;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MoneyTransfer::class)
        ->assertOk();
});
