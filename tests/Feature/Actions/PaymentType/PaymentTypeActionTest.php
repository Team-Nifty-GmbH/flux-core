<?php

use FluxErp\Actions\PaymentType\CreatePaymentType;
use FluxErp\Actions\PaymentType\DeletePaymentType;
use FluxErp\Actions\PaymentType\UpdatePaymentType;
use FluxErp\Models\PaymentType;

test('create payment type', function (): void {
    $type = CreatePaymentType::make([
        'name' => 'Wire Transfer',
        'tenants' => [$this->dbTenant->getKey()],
    ])->validate()->execute();

    expect($type)->toBeInstanceOf(PaymentType::class)
        ->name->toBe('Wire Transfer');
    expect($type->tenants)->toHaveCount(1);
});

test('create payment type auto-attaches default tenant', function (): void {
    $type = CreatePaymentType::make([
        'name' => 'Cash',
    ])->validate()->execute();

    expect($type->tenants)->not->toBeEmpty();
});

test('create payment type requires name', function (): void {
    CreatePaymentType::assertValidationErrors([], 'name');
});

test('update payment type', function (): void {
    $type = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    $updated = UpdatePaymentType::make([
        'id' => $type->getKey(),
        'name' => 'Direct Debit',
    ])->validate()->execute();

    expect($updated->name)->toBe('Direct Debit');
});

test('delete payment type', function (): void {
    $type = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    expect(DeletePaymentType::make(['id' => $type->getKey()])
        ->validate()->execute())->toBeTrue();
});
