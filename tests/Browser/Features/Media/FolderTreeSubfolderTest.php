<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\MediaFolder;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);

    $this->rootFolder = MediaFolder::create([
        'name' => 'Wurzelordner',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);
    $this->contact->mediaFolders()->attach($this->rootFolder->getKey());
});

test('subfolder can be created below a selected folder in the attachments tree', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();

    waitForElement($page, '[data-tab-name*="attachment"]');

    $page->script(<<<'JS'
        () => document.querySelector('[data-tab-name*="attachment"]').click()
    JS);

    waitForCondition($page, <<<'JS'
        () => Array.from(document.querySelectorAll('li, span, div'))
            .some(el => el.childElementCount === 0 && el.textContent?.trim() === 'Wurzelordner')
    JS, 15000);

    $page->script(<<<'JS'
        () => {
            const node = Array.from(document.querySelectorAll('li, span, div'))
                .find(el => el.childElementCount === 0 && el.textContent?.trim() === 'Wurzelordner');
            node.click();
        }
    JS);

    // the subfolder button lives in the selection action area, uniquely
    // identified by its x-show binding on multipleFileUpload
    waitForCondition($page, <<<'JS'
        () => {
            const btn = document.querySelector('button[x-show*="multipleFileUpload"]');
            return btn && btn.offsetParent !== null;
        }
    JS);

    $page->script(<<<'JS'
        () => document.querySelector('button[x-show*="multipleFileUpload"]').click()
    JS);

    waitForCondition($page, <<<'JS'
        () => Array.from(document.querySelectorAll('li, span, div'))
            .some(el => el.childElementCount === 0 && /^(Neuer Ordner|New folder)$/.test(el.textContent?.trim()))
    JS, 10000);

    $page->assertNoJavascriptErrors();

    expect(
        MediaFolder::query()
            ->where('parent_id', $this->rootFolder->getKey())
            ->exists()
    )->toBeTrue();
});
