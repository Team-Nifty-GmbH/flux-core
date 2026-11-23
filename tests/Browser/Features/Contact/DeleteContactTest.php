<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;

test('a tab request after deleting the contact does not send the locked contact id', function (): void {
    $contact = Contact::factory()->create();
    Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $page = visit(route('contacts.id?', ['id' => $contact->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            Livewire.interceptRequest(({ onRedirect }) => {
                onRedirect(({ preventDefault }) => preventDefault());
            });

            return Livewire.all()
                .find((component) => component.name === 'contact.contact')
                .$wire.delete();
        }
    JS);

    $result = $page->script(<<<'JS'
        () => {
            const addresses = Livewire.all()
                .find((component) => component.name === 'contact.addresses')
                .$wire;

            return addresses.$refresh().then(() => addresses.contact.id);
        }
    JS);

    expect($result)->toBe($contact->getKey());
});
