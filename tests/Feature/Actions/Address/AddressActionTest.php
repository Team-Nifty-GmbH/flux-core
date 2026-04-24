<?php

use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Address\DeleteAddress;
use FluxErp\Actions\Address\UpdateAddress;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
});

test('create address', function (): void {
    $address = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Test GmbH',
        'street' => 'Main Street 1',
        'city' => 'Berlin',
        'zip' => '10115',
    ])->validate()->execute();

    expect($address)
        ->toBeInstanceOf(Address::class)
        ->company->toBe('Test GmbH')
        ->contact_id->toBe($this->contact->getKey());
});

test('first address becomes main address automatically', function (): void {
    $address = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'First Address',
    ])->validate()->execute();

    expect($address)
        ->is_main_address->toBeTrue()
        ->is_invoice_address->toBeTrue()
        ->is_delivery_address->toBeTrue();
});

test('second address does not override main address flags', function (): void {
    Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);

    $second = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Second Address',
    ])->validate()->execute();

    expect($second)
        ->is_main_address->toBeFalse()
        ->is_invoice_address->toBeFalse()
        ->is_delivery_address->toBeFalse();
});

test('create address requires contact_id', function (): void {
    CreateAddress::assertValidationErrors(
        ['company' => 'Test'],
        'contact_id'
    );
});

test('create address strips email angle brackets', function (): void {
    $address = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'email' => 'John Doe <john@example.com>',
    ])->validate()->execute();

    expect($address->email)->toBe('john@example.com');
});

test('update address', function (): void {
    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
    ]);

    $updated = UpdateAddress::make([
        'id' => $address->getKey(),
        'company' => 'Updated Corp',
    ])->validate()->execute();

    expect($updated->company)->toBe('Updated Corp');
});

test('create address does not set is_payment_reminder_address automatically', function (): void {
    $address = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Only Address',
    ])->validate()->execute();

    expect($address->is_payment_reminder_address)->toBeFalse();
});

test('setting is_payment_reminder_address unsets it on other addresses', function (): void {
    $first = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'First',
        'is_payment_reminder_address' => true,
    ])->validate()->execute();

    $second = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Second',
        'is_payment_reminder_address' => true,
    ])->validate()->execute();

    $first->refresh();

    expect($second->is_payment_reminder_address)->toBeTrue()
        ->and($first->is_payment_reminder_address)->toBeFalse();
});

test('update address to is_payment_reminder_address unsets others', function (): void {
    $first = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'First',
        'is_payment_reminder_address' => true,
    ])->validate()->execute();

    $second = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Second',
    ])->validate()->execute();

    UpdateAddress::make([
        'id' => $second->getKey(),
        'is_payment_reminder_address' => true,
    ])->validate()->execute();

    $first->refresh();
    $second->refresh();

    expect($second->is_payment_reminder_address)->toBeTrue()
        ->and($first->is_payment_reminder_address)->toBeFalse();
});

test('no address needs is_payment_reminder_address', function (): void {
    $first = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'First',
    ])->validate()->execute();

    $second = CreateAddress::make([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Second',
    ])->validate()->execute();

    expect($first->is_payment_reminder_address)->toBeFalse()
        ->and($second->is_payment_reminder_address)->toBeFalse();
});

test('delete address', function (): void {
    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
    ]);

    $result = DeleteAddress::make(['id' => $address->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
});
