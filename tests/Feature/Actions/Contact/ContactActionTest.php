<?php

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Contact\DeleteContact;
use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;

test('create contact with defaults', function (): void {
    $contact = CreateContact::make([])
        ->validate()->execute();

    expect($contact)
        ->toBeInstanceOf(Contact::class)
        ->customer_number->not->toBeNull()
        ->payment_type_id->not->toBeNull()
        ->price_list_id->not->toBeNull();
});

test('create contact with main address', function (): void {
    $contact = CreateContact::make([
        'main_address' => [
            'company' => 'Test Corp',
            'firstname' => 'John',
            'lastname' => 'Doe',
        ],
    ])->validate()->execute();

    expect($contact->mainAddress)
        ->not->toBeNull()
        ->company->toBe('Test Corp');
});

test('create contact with tenant attachment', function (): void {
    $contact = CreateContact::make([
        'tenants' => [$this->dbTenant->getKey()],
    ])->validate()->execute();

    expect($contact->tenants)->toHaveCount(1);
});

test('create contact validates payment_type for tenant', function (): void {
    $otherTenant = FluxErp\Models\Tenant::factory()->create();
    $paymentType = PaymentType::factory()
        ->hasAttached($otherTenant, relationship: 'tenants')
        ->create();

    CreateContact::assertValidationErrors([
        'payment_type_id' => $paymentType->getKey(),
        'tenants' => [$this->dbTenant->getKey()],
    ], 'payment_type_id');
});

test('update contact', function (): void {
    $contact = Contact::factory()->create();

    $updated = UpdateContact::make([
        'id' => $contact->getKey(),
        'customer_number' => 'K-99999',
    ])->validate()->execute();

    expect($updated->customer_number)->toBe('K-99999');
});

test('update contact rejects duplicate customer_number', function (): void {
    $contact1 = Contact::factory()->create(['customer_number' => 'K-11111']);
    $contact2 = Contact::factory()->create();

    UpdateContact::assertValidationErrors([
        'id' => $contact2->getKey(),
        'customer_number' => 'K-11111',
    ], 'customer_number');
});

test('delete contact', function (): void {
    $contact = Contact::factory()->create();

    $result = DeleteContact::make(['id' => $contact->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
    expect(Contact::query()->whereKey($contact->getKey())->exists())->toBeFalse();
});
