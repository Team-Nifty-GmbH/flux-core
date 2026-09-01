<?php

use FluxErp\Livewire\Mobile\ShareTarget;

function fakeCapacitorScript(): string
{
    $image = imagecreatetruecolor(16, 16);
    imagefilledrectangle($image, 0, 0, 15, 15, imagecolorallocate($image, 200, 30, 30));
    ob_start();
    imagejpeg($image);
    $base64 = base64_encode(ob_get_clean());
    imagedestroy($image);

    $meta = json_encode([[
        'name' => 'rechnung.jpeg',
        'mimeType' => 'image/jpeg',
        'size' => 1234,
        'path' => 'shared_files/123_0_rechnung.jpeg',
    ]]);
    $metaJson = json_encode($meta);

    return <<<JS
        () => {
            window.__bridgeCalls = [];
            window.Capacitor = {
                Plugins: {
                    Preferences: {
                        get: async ({ key }) => {
                            window.__bridgeCalls.push('get:' + key);

                            return key === 'pending_shared_files'
                                ? { value: {$metaJson} }
                                : { value: null };
                        },
                        remove: async ({ key }) => { window.__bridgeCalls.push('remove:' + key); },
                    },
                    Filesystem: {
                        readFile: async ({ path }) => {
                            window.__bridgeCalls.push('read:' + path);

                            return { data: '{$base64}' };
                        },
                        rmdir: async ({ path }) => { window.__bridgeCalls.push('rmdir:' + path); },
                    },
                },
            };
        }
    JS;
}

test('shows bridge hint outside the mobile app', function (): void {
    visitLivewire(ShareTarget::class)
        ->assertNoSmoke()
        ->assertSee('This page is only available inside the Nuxbe mobile app.');
});

// The browser test server does not parse multipart bodies, so the actual file
// upload and action execution are covered by the Livewire test suite. This test
// covers the JS bridge wiring: reading the shared file metadata and content from
// the Capacitor plugins and handing the reconstructed File to the Livewire upload.
test('reads shared files from the bridge and starts the upload', function (): void {
    $page = visitLivewire(ShareTarget::class)->assertNoSmoke();

    $page->script(fakeCapacitorScript());

    $page->script(<<<'JS'
        () => {
            const root = document.querySelector('[data-testid="share-target"]');
            window.Alpine.$data(root).init();
        }
    JS);

    $result = json_decode($page->script(<<<'JS'
        () => new Promise((resolve) => {
            let attempts = 0;
            const check = () => {
                const root = document.querySelector('[data-testid="share-target"]');
                const state = window.Alpine.$data(root).state;

                if (['uploading', 'ready', 'error'].includes(state) || attempts++ > 100) {
                    resolve(JSON.stringify({ state, calls: window.__bridgeCalls }));

                    return;
                }
                setTimeout(check, 100);
            };
            check();
        })
    JS), true);

    expect($result['calls'])->toContain('get:pending_shared_files')
        ->toContain('read:shared_files/123_0_rechnung.jpeg')
        ->and($result['state'])->toBeIn(['uploading', 'ready']);
});
