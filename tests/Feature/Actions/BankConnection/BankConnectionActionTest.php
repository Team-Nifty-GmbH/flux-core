<?php

use FluxErp\Actions\BankConnection\CreateBankConnection;
use FluxErp\Actions\BankConnection\DeleteBankConnection;
use FluxErp\Actions\BankConnection\UpdateBankConnection;
use FluxErp\Models\BankConnection;

test('create bank connection', function (): void {
    $conn = CreateBankConnection::make(['name' => 'Business Account'])
        ->validate()->execute();

    expect($conn)->toBeInstanceOf(BankConnection::class)
        ->name->toBe('Business Account');
});

test('create bank connection requires name', function (): void {
    CreateBankConnection::assertValidationErrors([], 'name');
});

test('update bank connection', function (): void {
    $conn = BankConnection::factory()->create();

    $updated = UpdateBankConnection::make([
        'id' => $conn->getKey(),
        'name' => 'Savings Account',
    ])->validate()->execute();

    expect($updated->name)->toBe('Savings Account');
});

test('delete bank connection', function (): void {
    $conn = BankConnection::factory()->create();

    expect(DeleteBankConnection::make(['id' => $conn->getKey()])
        ->validate()->execute())->toBeTrue();
});
