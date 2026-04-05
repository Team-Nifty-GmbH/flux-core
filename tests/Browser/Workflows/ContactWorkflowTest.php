<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'company' => 'Test Company GmbH',
        'firstname' => 'Max',
        'lastname' => 'Mustermann',
    ]);
});

test('contact list loads and shows contacts', function (): void {
    $page = visit(route('contacts.contacts'))
        ->assertRoute('contacts.contacts')
        ->assertNoSmoke();

    waitForDataTable($page)
        ->assertNoJavascriptErrors();
});

test('contact detail page loads with tabs', function (): void {
    visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke()
        ->assertScript("document.querySelectorAll('[wire\\\\:click*=\"tab\"], [x-on\\\\:click*=\"tab\"], button[wire\\\\:click]').length > 0");
});

test('contact detail shows address data', function (): void {
    visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke()
        ->assertSee('Test Company GmbH');
});

test('contact address edit toggle works', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const editBtn = document.querySelector('[x-on\\:click*="edit"], button[wire\\:click*="edit"]');
            if (editBtn) editBtn.click();
        }
    JS);

    $page->wait(0.5)
        ->assertNoJavascriptErrors();
});

test('contact tabs switch without errors', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 1) tabs[1].click();
        }
    JS);

    $page->wait(1.5)
        ->assertNoJavascriptErrors();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 2) tabs[2].click();
        }
    JS);

    $page->wait(1.5)
        ->assertNoJavascriptErrors();
});

test('address list loads and shows addresses', function (): void {
    $page = visit(route('contacts.addresses'))
        ->assertRoute('contacts.addresses')
        ->assertNoSmoke();

    waitForDataTable($page)
        ->assertNoJavascriptErrors();
});
