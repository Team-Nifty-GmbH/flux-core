<?php

use FluxErp\Livewire\Contact\Accounting\CreditAccounts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CreditAccounts::class)
        ->assertOk();
});
