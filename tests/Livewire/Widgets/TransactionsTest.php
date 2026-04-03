<?php

use FluxErp\Livewire\Widgets\Transactions;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Transactions::class)
        ->assertOk();
})->skip('agent_id column missing - pending migration');
