<?php

use FluxErp\Livewire\Contact\Contact;
use FluxErp\Models\Address;
use FluxErp\Models\Contact as ContactModel;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = ContactModel::factory()->create();

    Address::factory()->create([
        'contact_id' => $this->contact->id,
        'is_delivery_address' => true,
        'is_invoice_address' => true,
        'is_main_address' => true,
    ]);
});

test('can delete contact', function (): void {
    Livewire::test(Contact::class, ['id' => $this->contact->id])
        ->call('delete')
        ->assertHasNoErrors()
        ->assertSet('contact.id', $this->contact->id)
        ->assertRedirectToRoute('contacts.contacts');
});

test('handles the deleted broadcast after deleting the contact', function (): void {
    $channel = $this->contact->broadcastChannel(false);

    Livewire::test(Contact::class, ['id' => $this->contact->id])
        ->call('delete')
        ->dispatch('echo-private:' . $channel . ',.ContactDeleted')
        ->assertRedirectToRoute('contacts.contacts');
});

test('renders successfully', function (): void {
    Livewire::test(Contact::class, ['id' => $this->contact->id])
        ->assertOk();
});

test('switch tabs', function (): void {
    Livewire::test(Contact::class, ['id' => $this->contact->id])->cycleTabs();
});

test('listens on the contact channel when an override mounts without the contact id', function (): void {
    $channel = $this->contact->broadcastChannel(false);

    Livewire::test(new class() extends Contact
    {
        public function mount(?int $id = null): void
        {
            $this->contact->fill(ContactModel::query()->whereKey($id)->firstOrFail());
        }
    }, ['id' => $this->contact->id])
        ->assertOk()
        ->dispatch('echo-private:' . $channel . ',.ContactDeleted')
        ->assertRedirectToRoute('contacts.contacts');
});
