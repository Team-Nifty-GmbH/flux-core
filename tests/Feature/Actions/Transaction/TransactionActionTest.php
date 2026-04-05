<?php

use FluxErp\Actions\Transaction\CreateTransaction;
use FluxErp\Actions\Transaction\DeleteTransaction;
use FluxErp\Actions\Transaction\UpdateTransaction;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Transaction;

beforeEach(function (): void {
    $this->bankConnection = BankConnection::factory()->create();
});

test('create transaction', function (): void {
    $tx = CreateTransaction::make([
        'bank_connection_id' => $this->bankConnection->getKey(),
        'value_date' => '2026-04-01',
        'booking_date' => '2026-04-01',
        'amount' => 500.00,
    ])->validate()->execute();

    expect($tx)->toBeInstanceOf(Transaction::class);
});

test('create transaction requires bank_connection or contact_bank_connection', function (): void {
    CreateTransaction::assertValidationErrors([
        'value_date' => '2026-04-01',
        'booking_date' => '2026-04-01',
        'amount' => 100,
    ], 'bank_connection_id');
});

test('update transaction', function (): void {
    $tx = Transaction::factory()->create([
        'bank_connection_id' => $this->bankConnection->getKey(),
    ]);

    $updated = UpdateTransaction::make([
        'id' => $tx->getKey(),
        'purpose' => 'Invoice payment',
    ])->validate()->execute();

    expect($updated->purpose)->toBe('Invoice payment');
});

test('delete transaction', function (): void {
    $tx = Transaction::factory()->create([
        'bank_connection_id' => $this->bankConnection->getKey(),
    ]);

    expect(DeleteTransaction::make(['id' => $tx->getKey()])
        ->validate()->execute())->toBeTrue();
});
