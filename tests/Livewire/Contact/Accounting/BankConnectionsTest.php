<?php

use FluxErp\Livewire\Contact\Accounting\BankConnections;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(BankConnections::class)
        ->assertOk();
});
