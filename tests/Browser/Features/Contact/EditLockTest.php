<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\PendingAwaitablePage;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
});

/**
 * Wait until the contact component's edit state matches the expectation.
 */
function waitForContactEditState(PendingAwaitablePage|AwaitableWebpage $page, bool $expected): void
{
    $expectedJson = $expected ? 'true' : 'false';

    $page->script(<<<JS
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(
                () => reject(new Error('Contact edit state never became {$expectedJson}')),
                10000,
            );
            const check = () => {
                const root = document
                    .getElementById('contact')
                    .closest('[wire\\\\:id]');
                const wire = Livewire.find(root.getAttribute('wire:id'));

                if (wire.edit === {$expectedJson}) {
                    clearTimeout(timeout);
                    // Let Alpine flush the effects that react to the state change.
                    setTimeout(resolve, 250);
                } else {
                    setTimeout(check, 100);
                }
            };
            check();
        })
    JS);
}

/**
 * The accounting tab is locked via "pointer-events-none" while not editing,
 * so hit test the field instead of trusting the class name.
 */
function vatFieldIsLocked(PendingAwaitablePage|AwaitableWebpage $page): bool
{
    return $page->script(<<<'JS'
        () => {
            const input = document.querySelector(
                'input[wire\\:model="contact.vat_id"]',
            );

            if (! input) {
                throw new Error('VAT input not found on the accounting tab');
            }

            input.scrollIntoView({ block: 'center' });

            const rect = input.getBoundingClientRect();
            const hit = document.elementFromPoint(
                rect.left + rect.width / 2,
                rect.top + rect.height / 2,
            );

            return hit !== input;
        }
    JS);
}

test('accounting fields stay interactive after edit, save and edit', function (): void {
    $page = visit(route('contacts.id?', ['id' => $this->contact->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(
                () => reject(new Error('Accounting tab did not render')),
                10000,
            );
            const check = () => {
                if (document.querySelector('input[wire\\:model="contact.vat_id"]')) {
                    clearTimeout(timeout);
                    resolve();
                } else {
                    document
                        .querySelector('[data-tab-name="contact.accounting"]')
                        ?.click();
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    expect(vatFieldIsLocked($page))->toBeTrue();

    $page->click('Edit');
    waitForContactEditState($page, true);
    expect(vatFieldIsLocked($page))->toBeFalse();

    $page->click('Save');
    waitForContactEditState($page, false);
    expect(vatFieldIsLocked($page))->toBeTrue();

    $page->click('Edit');
    waitForContactEditState($page, true);
    expect(vatFieldIsLocked($page))->toBeFalse();
});
