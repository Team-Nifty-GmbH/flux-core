<?php

use FluxErp\Actions\ContactOption\CreateContactOption;
use FluxErp\Actions\ContactOption\DeleteContactOption;
use FluxErp\Actions\ContactOption\UpdateContactOption;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create(['contact_id' => $this->contact->getKey()]);
});

test('create contact option', function (): void {
    $option = CreateContactOption::make([
        'address_id' => $this->address->getKey(),
        'type' => 'phone',
        'label' => 'Mobile',
        'value' => '+49 123 456789',
    ])->validate()->execute();

    expect($option->value)->toBe('+49 123 456789');
});

test('create contact option requires address_id type label value', function (): void {
    CreateContactOption::assertValidationErrors([], ['address_id', 'type', 'label', 'value']);
});

test('update contact option', function (): void {
    $option = CreateContactOption::make([
        'address_id' => $this->address->getKey(),
        'type' => 'phone',
        'label' => 'Mobile',
        'value' => '+49 123 456789',
    ])->validate()->execute();

    $updated = UpdateContactOption::make([
        'id' => $option->getKey(),
        'value' => '+49 999 888777',
    ])->validate()->execute();

    expect($updated->value)->toBe('+49 999 888777');
});

test('delete contact option', function (): void {
    $option = CreateContactOption::make([
        'address_id' => $this->address->getKey(),
        'type' => 'email',
        'label' => 'Work',
        'value' => 'test@example.com',
    ])->validate()->execute();

    expect(DeleteContactOption::make(['id' => $option->getKey()])
        ->validate()->execute())->toBeTrue();
});
