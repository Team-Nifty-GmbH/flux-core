<?php

use FluxErp\Livewire\Settings\LedgerAccounts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LedgerAccounts::class)
        ->assertOk();
});
