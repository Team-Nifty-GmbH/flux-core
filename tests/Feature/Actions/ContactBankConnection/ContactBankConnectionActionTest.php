<?php

use FluxErp\Actions\ContactBankConnection\CreateContactBankConnection;
use FluxErp\Actions\ContactBankConnection\DeleteContactBankConnection;
use FluxErp\Actions\ContactBankConnection\UpdateContactBankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
});

test('create contact bank connection with iban', function (): void {
    $conn = CreateContactBankConnection::make([
        'contact_id' => $this->contact->getKey(),
        'iban' => 'DE89370400440532013000',
    ])->validate()->execute();

    expect($conn)->toBeInstanceOf(ContactBankConnection::class)
        ->iban->toBe('DE89370400440532013000');
});

test('update contact bank connection', function (): void {
    $conn = ContactBankConnection::factory()->create([
        'contact_id' => $this->contact->getKey(),
    ]);

    $updated = UpdateContactBankConnection::make([
        'id' => $conn->getKey(),
        'account_holder' => 'John Doe',
    ])->validate()->execute();

    expect($updated->account_holder)->toBe('John Doe');
});

test('delete contact bank connection', function (): void {
    $conn = ContactBankConnection::factory()->create([
        'contact_id' => $this->contact->getKey(),
    ]);

    expect(DeleteContactBankConnection::make(['id' => $conn->getKey()])
        ->validate()->execute())->toBeTrue();
});
