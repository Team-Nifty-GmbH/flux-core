<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');

    $this->contact = Contact::factory()->create();
    Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);

    $this->contact
        ->addMedia(UploadedFile::fake()->create('vorlage.xlsx'))
        ->toMediaCollection('Berater');
});

test('a node added to the tree does not reach the locked livewire property', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();

    waitForElement($page, '[data-tab-name*="attachment"]');

    $page->script(<<<'JS'
        () => document.querySelector('[data-tab-name*="attachment"]').click()
    JS);

    waitForCondition($page, <<<'JS'
        () => {
            const host = document.querySelector('[x-data^="folder_tree"]');

            return !! window.Alpine && !! host && Array.isArray(Alpine.$data(host)?.tree)
                && Alpine.$data(host).tree.length > 0;
        }
    JS, 15000);

    // The upload handlers push the new file into the node they are on. As long as the
    // tree is the very array behind the locked mediaTree property, that push marks the
    // property dirty and the next Livewire request dies with
    // CannotUpdateLockedPropertyException.
    $counts = $page->script(<<<'JS'
        () => {
            const host = document.querySelector('[x-data^="folder_tree"]');
            const data = Alpine.$data(host);
            const wire = Livewire.all()
                .map((component) => component.$wire)
                .find((wire) => Array.isArray(wire.mediaTree));

            const node = data.tree.find((node) => Array.isArray(node.children));
            const before = wire.mediaTree.find((node) => Array.isArray(node.children)).children.length;

            node.children.push({ id: 'probe', name: 'probe' });

            return {
                local: node.children.length,
                server: wire.mediaTree.find((node) => Array.isArray(node.children)).children.length,
                before: before,
            };
        }
    JS);

    expect($counts['local'])->toBe($counts['before'] + 1)
        ->and($counts['server'])->toBe($counts['before']);
});
