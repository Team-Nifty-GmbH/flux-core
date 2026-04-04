<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactOption;

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

    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Contact list did not render')), 10000);
            const check = () => {
                if (document.querySelectorAll('tbody tr').length > 0) {
                    clearTimeout(timeout);
                    resolve();
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    $page->assertNoJavascriptErrors();
});

test('contact detail page loads with tabs', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();


    $tabCount = $page->script(<<<'JS'
        () => document.querySelectorAll('[wire\\:click*="tab"], [x-on\\:click*="tab"], button[wire\\:click]').length
    JS);

    expect($tabCount)->toBeGreaterThan(0);
    $page->assertNoJavascriptErrors();
});

test('contact detail shows address data', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();


    $page->assertSee('Test Company GmbH');
    $page->assertNoJavascriptErrors();
});

test('contact address edit toggle works', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();


    // Find and click edit button
    $page->script(<<<'JS'
        () => {
            const editBtn = document.querySelector('[x-on\\:click*="edit"], button[wire\\:click*="edit"]');
            if (editBtn) editBtn.click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 500))');
    $page->assertNoJavascriptErrors();
});

test('contact tabs switch without errors', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();


    // Click through all visible tabs
    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 1) tabs[1].click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1500))');
    $page->assertNoJavascriptErrors();

    // Click third tab if available
    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 2) tabs[2].click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1500))');
    $page->assertNoJavascriptErrors();
});

test('address list loads and shows addresses', function (): void {
    $page = visit(route('contacts.addresses'))
        ->assertRoute('contacts.addresses')
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Address list did not render')), 10000);
            const check = () => {
                if (document.querySelectorAll('tbody tr').length > 0) {
                    clearTimeout(timeout);
                    resolve();
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    $page->assertNoJavascriptErrors();
});
