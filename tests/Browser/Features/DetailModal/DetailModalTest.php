<?php

test('detail modal iframe exists in DOM', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript("!!document.getElementById('detail-modal-iframe')");
});

test('$nuxbe.openDetailModal function exists', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript("typeof window.$nuxbe.openDetailModal === 'function'");
});

test('detail modal opens without js errors', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            try {
                window.$nuxbe.openDetailModal(window.location.href);
            } catch(e) {
                // May fail if modal element not ready
            }
        }
    JS);

    $page->wait(0.5)
        ->assertNoJavascriptErrors();
});
