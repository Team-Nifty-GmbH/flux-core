<?php

use FluxErp\Livewire\Contact\Accounting\CreditAccounts;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(CreditAccounts::class, ['contactId' => $contact->getKey()])
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(CreditAccounts::class, ['contactId' => $contact->getKey()])
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('contactBankConnection.id', null)
        ->assertSet('contactBankConnection.iban', null)
        ->assertSet('contactBankConnection.account_holder', null)
        ->assertSet('contactBankConnection.bank_name', null)
        ->assertOpensModal('edit-contact-bank-connection');
});

test('edit with model fills form and opens modal', function (): void {
    $contact = Contact::factory()->create();
    $bankConnection = ContactBankConnection::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_credit_account' => true,
    ]);

    Livewire::test(CreditAccounts::class, ['contactId' => $contact->getKey()])
        ->call('edit', $bankConnection->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('contactBankConnection.id', $bankConnection->getKey())
        ->assertSet('contactBankConnection.iban', $bankConnection->iban)
        ->assertSet('contactBankConnection.bank_name', $bankConnection->bank_name)
        ->assertOpensModal('edit-contact-bank-connection');
});

test('can create credit account', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(CreditAccounts::class, ['contactId' => $contact->getKey()])
        ->call('edit', null)
        ->set('contactBankConnection.bank_name', 'Test Credit Account')
        ->set('contactBankConnection.account_holder', 'John Doe')
        ->set('contactBankConnection.iban', 'DE89370400440532013000')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('contact_bank_connections', [
        'contact_id' => $contact->getKey(),
        'bank_name' => 'Test Credit Account',
        'is_credit_account' => true,
    ]);
});

test('can update credit account', function (): void {
    $contact = Contact::factory()->create();
    $bankConnection = ContactBankConnection::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_credit_account' => true,
        'bic' => null,
    ]);

    Livewire::test(CreditAccounts::class, ['contactId' => $contact->getKey()])
        ->call('edit', $bankConnection->getKey())
        ->assertSet('contactBankConnection.id', $bankConnection->getKey())
        ->set('contactBankConnection.bank_name', 'Updated Credit Account')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($bankConnection->refresh()->bank_name)->toEqual('Updated Credit Account');
});

test('can delete credit account', function (): void {
    $contact = Contact::factory()->create();
    $bankConnection = ContactBankConnection::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_credit_account' => true,
    ]);

    Livewire::test(CreditAccounts::class, ['contactId' => $contact->getKey()])
        ->call('delete', $bankConnection->getKey())
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertSoftDeleted('contact_bank_connections', [
        'id' => $bankConnection->getKey(),
    ]);
});

test('create transaction opens modal', function (): void {
    $contact = Contact::factory()->create();
    $bankConnection = ContactBankConnection::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_credit_account' => true,
    ]);

    Livewire::test(CreditAccounts::class, ['contactId' => $contact->getKey()])
        ->call('createTransaction', $bankConnection->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('transactionForm.contact_bank_connection_id', $bankConnection->getKey())
        ->assertSet('transactionForm.booking_date', now()->format('Y-m-d'))
        ->assertSet('transactionForm.value_date', now()->format('Y-m-d'))
        ->assertExecutesJs("\$tsui.open.modal('transaction-details-modal')");
});

test('can save transaction', function (): void {
    $contact = Contact::factory()->create();
    $bankConnection = ContactBankConnection::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_credit_account' => true,
    ]);

    Livewire::test(CreditAccounts::class, ['contactId' => $contact->getKey()])
        ->call('createTransaction', $bankConnection->getKey())
        ->set('transactionForm.amount', 100.50)
        ->set('transactionForm.purpose', 'Test Transaction')
        ->call('saveTransaction')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('transactions', [
        'contact_bank_connection_id' => $bankConnection->getKey(),
        'amount' => 100.50,
        'purpose' => 'Test Transaction',
    ]);
});

test('save transaction validation fails with missing required fields', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(CreditAccounts::class, ['contactId' => $contact->getKey()])
        ->set('transactionForm.amount', null)
        ->set('transactionForm.booking_date', null)
        ->set('transactionForm.value_date', null)
        ->call('saveTransaction')
        ->assertOk()
        ->assertReturned(false);
});

test('save credit account validation fails with missing contact', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(CreditAccounts::class, ['contactId' => $contact->getKey()])
        ->set('contactBankConnection.iban', 'invalid-iban')
        ->call('save')
        ->assertOk()
        ->assertReturned(false);
});
