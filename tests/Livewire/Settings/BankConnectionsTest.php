<?php

use FluxErp\Livewire\Settings\BankConnections;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(BankConnections::class)
        ->assertOk();
});
