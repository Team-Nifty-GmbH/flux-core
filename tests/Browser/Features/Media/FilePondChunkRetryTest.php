<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');

    $this->contact = Contact::factory()->create();
    Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
});

test('a chunk whose response got lost is resumed at the server offset', function (int $lostPatch, array $expectedPatches): void {
    $page = visit('/contacts/contacts/' . $this->contact->getKey())
        ->assertNoSmoke();

    waitForElement($page, '[data-tab-name*="attachment"]');

    $page->script(<<<'JS'
        () => document.querySelector('[data-tab-name*="attachment"]').click()
    JS);

    waitForCondition($page, <<<'JS'
        () => {
            const root = document.querySelector('.filepond--root');

            return !! window.Alpine && !! root && !! Alpine.$data(root)?.pond;
        }
    JS, 15000);

    $page->script(str_replace('LOST_PATCH', (string) $lostPatch, <<<'JS'
        async () => {
            window.chunkPatches = [];
            const originalFetch = window.fetch;
            window.fetch = async (url, options) => {
                if (options?.method !== 'PATCH') {
                    return originalFetch(url, options);
                }

                const response = await originalFetch(url, options);
                window.chunkPatches.push(response.status);

                if (window.chunkPatches.length === LOST_PATCH) {
                    throw new TypeError('Failed to fetch');
                }

                return response;
            };

            const pond = Alpine.$data(document.querySelector('.filepond--root')).pond;

            await pond.addFile(new File([new Uint8Array(2.5 * 1024 * 1024)], 'foto.txt', { type: 'text/plain' }));
        }
    JS));

    waitForCondition($page, <<<'JS'
        () => {
            const data = Alpine.$data(document.querySelector('.filepond--root'));

            return [5, 6].includes(data.pond.getFiles()[0]?.status);
        }
    JS, 20000);

    $state = $page->script(<<<'JS'
        () => {
            const data = Alpine.$data(document.querySelector('.filepond--root'));

            return {
                status: data.pond.getFiles()[0].status,
                patches: window.chunkPatches,
                temp: data.tempFilesId.length,
            };
        }
    JS);

    expect($state['status'])->toBe(5)
        ->and($state['patches'])->toBe($expectedPatches)
        ->and($state['temp'])->toBe(1);
})->with([
    'first chunk' => [1, [200, 409, 200, 200]],
    'last chunk' => [3, [200, 200, 200, 404]],
]);
