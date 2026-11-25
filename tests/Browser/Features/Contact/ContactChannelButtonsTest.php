<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;

test('contact channel buttons do not cover the input text', function (): void {
    $contact = Contact::factory()->create();
    Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'email_primary' => 'someone@example.com',
        'phone' => '0123456789',
        'phone_mobile' => '0987654321',
        'url' => 'example.com',
    ]);

    $page = visit(route('contacts.id?', ['id' => $contact->getKey()]))
        ->assertNoSmoke();

    $overlaps = $page->script(<<<'JS'
        () => ['email_primary', 'phone', 'phone_mobile', 'url'].map((field) => {
            const input = document.querySelector(
                'input[wire\\:model="address.' + field + '"]',
            );
            const button = input.parentElement.querySelector('button');
            const textStart = input.getBoundingClientRect().left
                + parseFloat(getComputedStyle(input).paddingLeft);

            return button.getBoundingClientRect().right > textStart ? field : null;
        }).filter(Boolean)
    JS);

    expect($overlaps)->toBe([]);
});
