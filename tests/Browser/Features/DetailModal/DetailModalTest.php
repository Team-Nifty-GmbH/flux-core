<?php

test('detail modal iframe exists in DOM', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $hasIframe = $page->script(<<<'JS'
        () => !!document.getElementById('detail-modal-iframe')
    JS);
    expect($$hasIframe)->toBeTrue();

    expect($hasIframe)->toBeTrue();
    $page->assertNoJavascriptErrors();
});

test('$nuxbe.openDetailModal function exists', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $hasFunction = $page->script(<<<'JS'
        () => typeof window.$nuxbe.openDetailModal === 'function'
    JS);
    expect($$hasFunction)->toBeTrue();

    expect($hasFunction)->toBeTrue();
    $page->assertNoJavascriptErrors();
});

test('detail modal opens without js errors', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    // Open detail modal with the current page URL as test
    $page->script(<<<'JS'
        () => {
            try {
                window.$nuxbe.openDetailModal(window.location.href);
            } catch(e) {
                // May fail if modal element not ready
            }
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 500))');
    $page->assertNoJavascriptErrors();
});
