<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'company' => 'Interaktions Test GmbH',
        'firstname' => 'Hans',
        'lastname' => 'Tester',
        'street' => 'Teststraße 1',
        'city' => 'Teststadt',
        'zip' => '12345',
    ]);
});

test('contact detail shows address data', function (): void {
    visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke()
        ->assertSee('Interaktions Test GmbH');
});

test('contact detail shows address street', function (): void {
    visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke()
        ->assertSee('Teststraße 1');
});

test('contact detail address fields are present', function (): void {
    visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke()
        ->assertScript('!!document.querySelector("input[wire\\\\:model*=\\"address.company\\"]")');
});

test('contact orders tab loads', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();

    clickTab($page, 'Orders', 'Aufträge')
        ->assertNoJavascriptErrors();
});

test('contact accounting tab loads', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();

    clickTab($page, 'Accounting', 'Buchhaltung')
        ->assertNoJavascriptErrors();
});

test('contact communication tab loads', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();

    clickTab($page, 'Communication', 'Kommunikation')
        ->assertNoJavascriptErrors();
});

test('contact attachments tab loads', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();

    clickTab($page, 'Attachments', 'Anhänge')
        ->assertNoJavascriptErrors();
});

test('contact activities tab loads', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();

    clickTab($page, 'Activities', 'Aktivitäten')
        ->assertNoJavascriptErrors();
});

test('contact new address button exists', function (): void {
    visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke()
        ->assertScript(<<<'JS'
            !!Array.from(document.querySelectorAll('button')).find(b =>
                b.textContent?.includes('New') || b.textContent?.includes('Neu')
            )
        JS);
});

test('address list page loads', function (): void {
    visit(route('contacts.addresses'))
        ->assertRoute('contacts.addresses')
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('communications list page loads', function (): void {
    visit(route('contacts.communications'))
        ->assertRoute('contacts.communications')
        ->assertNoSmoke();
});
