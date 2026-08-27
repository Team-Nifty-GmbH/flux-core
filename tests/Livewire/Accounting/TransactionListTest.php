<?php

use FluxErp\Livewire\Accounting\TransactionList;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Transaction;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TransactionList::class)
        ->assertOk();
});

test('delete selected deletes the selected transactions', function (): void {
    $transaction = Transaction::factory()->create([
        'bank_connection_id' => BankConnection::factory()->create()->getKey(),
    ]);

    Livewire::test(TransactionList::class)
        ->set('selected', [$transaction->getKey()])
        ->call('deleteSelected')
        ->assertOk()
        ->assertSet('selected', []);

    $this->assertSoftDeleted($transaction);
});
