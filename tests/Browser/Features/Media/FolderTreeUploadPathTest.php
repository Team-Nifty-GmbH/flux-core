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

    // A file living in the nested virtual collection "Berater.1 Update" makes the
    // tree render a "Berater" folder containing a "1 Update" subfolder. The
    // subfolder node's slug is the full dotted path "Berater.1 Update".
    $this->contact
        ->addMedia(UploadedFile::fake()->create('vorlage.xlsx'))
        ->toMediaCollection('Berater.1 Update');
});

test('getNodePath returns a nested folder slug without duplicating ancestor segments', function (): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();

    waitForElement($page, '[data-tab-name*="attachment"]');

    $page->script(<<<'JS'
        () => document.querySelector('[data-tab-name*="attachment"]').click()
    JS);

    // wait until a folder-tree Alpine scope exposing getNodePath has loaded the tree
    waitForCondition($page, <<<'JS'
        () => {
            if (!window.Alpine) return false;
            return Array.from(document.querySelectorAll('[x-data]')).some((el) => {
                const data = Alpine.$data(el);
                return data
                    && typeof data.getNodePath === 'function'
                    && Array.isArray(data.tree)
                    && data.tree.length > 0;
            });
        }
    JS, 15000);

    $path = $page->script(<<<'JS'
        () => {
            const host = Array.from(document.querySelectorAll('[x-data]')).find((el) => {
                const data = Alpine.$data(el);
                return data && typeof data.getNodePath === 'function' && Array.isArray(data.tree);
            });
            const data = Alpine.$data(host);
            const find = (nodes, slug) => {
                for (const node of nodes) {
                    if (node.slug === slug) return node;
                    if (node.children) {
                        const hit = find(node.children, slug);
                        if (hit) return hit;
                    }
                }
                return null;
            };
            const node = find(data.tree, 'Berater.1 Update');
            return data.getNodePath(node, 'slug');
        }
    JS);

    expect($path)->toBe('Berater.1 Update');
});
