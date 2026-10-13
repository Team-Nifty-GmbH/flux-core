<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
    $this->defaultLanguage->update(['language_code' => 'de']);

    // every upload runs into a 429, like the 61st file within a minute under throttle:60,1
    app()->bind('upload-always-throttled', fn () => new class()
    {
        public function handle(Request $request, Closure $next): never
        {
            abort(429);
        }
    });
    config(['livewire.temporary_file_upload.middleware' => 'upload-always-throttled']);

    $this->contact = Contact::factory()->create();
    Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
});

test('a file rejected with 429 stays in the pond with its reason and does not block saving', function (): void {
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

    $page->script(<<<'JS'
        async () => {
            const pond = Alpine.$data(document.querySelector('.filepond--root')).pond;

            await pond.addFile(new File(['content'], 'vertrag.txt', { type: 'text/plain' }));
        }
    JS);

    waitForCondition($page, <<<'JS'
        () => {
            const data = Alpine.$data(document.querySelector('.filepond--root'));

            return data.pond.getFiles().length === 0
                || (data.pond.getFiles()[0].status === 6 && data.isLoadingFiles.length === 0);
        }
    JS, 15000);

    $state = $page->script(<<<'JS'
        () => {
            const data = Alpine.$data(document.querySelector('.filepond--root'));

            return {
                files: data.pond.getFiles().map((file) => file.filename),
                label: document.querySelector('.filepond--item[data-filepond-item-state="processing-error"] .filepond--file-status-main')?.textContent,
                loading: data.isLoadingFiles.length,
                temp: data.tempFilesId.length,
            };
        }
    JS);

    expect($state['files'])->toBe(['vertrag.txt'])
        ->and($state['label'])->toBe('Zu viele Dateien gleichzeitig, bitte erneut versuchen')
        ->and($state['loading'])->toBe(0)
        ->and($state['temp'])->toBe(0);
});
