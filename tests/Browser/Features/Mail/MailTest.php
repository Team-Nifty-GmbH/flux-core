<?php

use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\PendingAwaitablePage;

/**
 * Headless Chromium starts with its cursor at 0,0, which is on the sidebar, and
 * fires a mouseover there once the page has loaded. When Alpine is listening by
 * then, the sidebar expands over the folder column and covers the New Email
 * button; the click waits for the button forever and never moves the cursor
 * away. Moving the cursor into the content area closes the sidebar again.
 */
function visitMailPage(): AwaitableWebpage|PendingAwaitablePage
{
    return visit(route('mail'))
        ->assertNoSmoke()
        ->hover('main >> nth=0');
}

test('mail page loads without js errors', function (): void {
    visit(route('mail'))
        ->assertNoSmoke();
});

test('new email button opens a blank compose modal', function (): void {
    $page = visitMailPage();

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
    $contact = FluxErp\Models\Contact::factory()->create();
    FluxErp\Models\Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'suggestme@example.com',
        'is_main_address' => true,
    ]);

    $page = visitMailPage();

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
    $contact = FluxErp\Models\Contact::factory()->create();
    FluxErp\Models\Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'pickme@example.com',
        'is_main_address' => true,
    ]);

    $page = visitMailPage();

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

    $page->click('li[dusk="flux_pillbox_option"]');

    waitForCondition(
        $page,
        "() => [...document.querySelectorAll('#edit-mail span')]
            .some((item) => item.textContent.trim() === 'pickme@example.com')",
        10000
    )->assertNoJavascriptErrors();
});

test('typing a free email address adds a recipient pill on enter', function (): void {
    $page = visitMailPage();

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

test('recipient pill survives closing an autocomplete dropdown', function (): void {
    $page = visitMailPage();

    $page->click(__('New Email'));

    waitForCondition(
        $page,
        "() => {
            const modal = document.getElementById('edit-mail');

            return modal && getComputedStyle(modal).display !== 'none';
        }",
        10000
    );

    $page->fill('#edit-mail input >> nth=0', 'keepme@example.com');
    $page->keys('#edit-mail input >> nth=0', 'Enter');

    waitForCondition(
        $page,
        "() => [...document.querySelectorAll('#edit-mail span')]
            .some((item) => item.textContent.trim() === 'keepme@example.com')",
        10000
    );

    // open the bcc dropdown, then close it again - the bubbling close
    // event must not clear the form
    $page->fill('#edit-mail input >> nth=2', 'something');
    $page->script('() => new Promise((r) => setTimeout(r, 600))');
    $page->keys('#edit-mail input >> nth=2', 'Escape');
    $page->script('() => new Promise((r) => setTimeout(r, 1500))');

    waitForCondition(
        $page,
        "() => [...document.querySelectorAll('#edit-mail span')]
            .some((item) => item.textContent.trim() === 'keepme@example.com')",
        5000
    )->assertNoJavascriptErrors();
});
