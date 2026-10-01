<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Printer;

test('choosing a printer fills the size select of the same create documents modal', function (): void {
    $contact = Contact::factory()->create();
    Address::factory()->create(['contact_id' => $contact->getKey(), 'is_main_address' => true]);

    $this->user->printers()->attach(
        Printer::query()
            ->create([
                'name' => 'Printer A',
                'spooler_name' => 'test',
                'media_sizes' => ['A4', 'A5'],
                'is_active' => true,
                'is_visible' => true,
            ])
            ->getKey()
    );

    // The communication tab brings a second create documents modal onto the contact page
    $page = visit(route('contacts.id?', ['id' => $contact->getKey()]) . '?tab=contact.communication')
        ->assertNoSmoke();

    waitForCondition(
        $page,
        "() => document.querySelectorAll('[id^=\"create-documents-\"]').length === 2",
        15000
    );

    $modal = '#' . $page->script(<<<'JS'
        () => {
            const component = Livewire.all().find((c) => c.name === 'contact.communication');
            component.$wire.set('selectedPrintLayouts.print', ['any'], false);
            $tsui.open.modal('create-documents-' + component.id.toLowerCase());

            return 'create-documents-' + component.id.toLowerCase();
        }
    JS);

    waitForElement($page, $modal . ' div[x-data*="printJobForm.printer_id"] button[x-ref="button"]', 15000);

    $page->click($modal . ' div[x-data*="printJobForm.printer_id"] button[x-ref="button"]')
        ->click('li[role="option"]:visible:has-text("Printer A")');

    waitForElement($page, $modal . ' div[x-data*="printJobForm.size"] button[x-ref="button"]');

    $page->click($modal . ' div[x-data*="printJobForm.size"] button[x-ref="button"]')
        ->click('li[role="option"]:visible:has-text("A5")')
        ->assertNoSmoke();
});
