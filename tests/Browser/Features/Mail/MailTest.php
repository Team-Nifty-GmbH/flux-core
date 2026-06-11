<?php

test('mail page loads without js errors', function (): void {
    visit(route('mail'))
        ->assertNoSmoke();
});

test('new email button opens a blank compose modal', function (): void {
    $page = visit(route('mail'))
        ->assertNoSmoke();

    $page->click(__('New Email'));

    waitForCondition(
        $page,
        "() => {
            const modal = document.getElementById('edit-mail');

            return modal && getComputedStyle(modal).display !== 'none';
        }",
        10000
    )->assertNoJavascriptErrors();
});

test('compose to field suggests matching addresses', function (): void {
    $contact = \FluxErp\Models\Contact::factory()->create();
    \FluxErp\Models\Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'suggestme@example.com',
        'is_main_address' => true,
    ]);

    $page = visit(route('mail'))
        ->assertNoSmoke();

    $page->click(__('New Email'));

    waitForCondition(
        $page,
        "() => {
            const modal = document.getElementById('edit-mail');

            return modal && getComputedStyle(modal).display !== 'none';
        }",
        10000
    );

    $page->fill('#edit-mail input >> nth=0', 'suggestme');

    waitForCondition(
        $page,
        "() => [...document.querySelectorAll('li')]
            .some((item) => item.textContent.includes('suggestme@example.com'))",
        10000
    )->assertNoJavascriptErrors();
});

test('selecting a suggestion adds a recipient pill', function (): void {
    $contact = \FluxErp\Models\Contact::factory()->create();
    \FluxErp\Models\Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'pickme@example.com',
        'is_main_address' => true,
    ]);

    $page = visit(route('mail'))
        ->assertNoSmoke();

    $page->click(__('New Email'));

    waitForCondition(
        $page,
        "() => {
            const modal = document.getElementById('edit-mail');

            return modal && getComputedStyle(modal).display !== 'none';
        }",
        10000
    );

    $page->fill('#edit-mail input >> nth=0', 'pickme');

    waitForCondition(
        $page,
        "() => [...document.querySelectorAll('li')]
            .some((item) => item.textContent.includes('pickme@example.com'))",
        10000
    );

    $page->click('li[dusk="tallstackui_autocomplete_option"]');

    waitForCondition(
        $page,
        "() => [...document.querySelectorAll('#edit-mail span')]
            .some((item) => item.textContent.trim() === 'pickme@example.com')",
        10000
    )->assertNoJavascriptErrors();
});

test('typing a free email address adds a recipient pill on enter', function (): void {
    $page = visit(route('mail'))
        ->assertNoSmoke();

    $page->click(__('New Email'));

    waitForCondition(
        $page,
        "() => {
            const modal = document.getElementById('edit-mail');

            return modal && getComputedStyle(modal).display !== 'none';
        }",
        10000
    );

    $page->fill('#edit-mail input >> nth=0', 'freetext@example.com');
    $page->keys('#edit-mail input >> nth=0', 'Enter');

    waitForCondition(
        $page,
        "() => [...document.querySelectorAll('#edit-mail span')]
            .some((item) => item.textContent.trim() === 'freetext@example.com')",
        10000
    )->assertNoJavascriptErrors();
});
