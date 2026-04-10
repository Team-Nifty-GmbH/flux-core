<?php

use FluxErp\Livewire\Contact\Accounting\BankConnections;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(BankConnections::class, ['contactId' => $contact->getKey()])
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(BankConnections::class, ['contactId' => $contact->getKey()])
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('contactBankConnection.id', null)
        ->assertSet('contactBankConnection.iban', null)
        ->assertSet('contactBankConnection.account_holder', null)
        ->assertOpensModal('edit-contact-bank-connection');
});

test('edit with model fills form', function (): void {
    $contact = Contact::factory()->create();
    $bankConnection = ContactBankConnection::factory()->create([
        'contact_id' => $contact->getKey(),
    ]);

    Livewire::test(BankConnections::class, ['contactId' => $contact->getKey()])
        ->call('edit', $bankConnection->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('contactBankConnection.id', $bankConnection->getKey())
        ->assertSet('contactBankConnection.iban', $bankConnection->iban)
        ->assertOpensModal('edit-contact-bank-connection');
});
