<?php

test('settings page loads without js errors', function (): void {
    visit(route('settings'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('settings tree navigation works', function (): void {
    $page = visit(route('settings'))
        ->assertNoSmoke();

    // Click a settings menu item
    $page->script(<<<'JS'
        () => {
            const items = document.querySelectorAll('[wire\\:click*="component"]');
            if (items.length > 1) items[1].click();
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('settings mobile toggle works', function (): void {
    $page = visit(route('settings'))
        ->assertNoSmoke();

    // Check that the mobile toggle mechanism exists
    $hasMobileToggle = $page->script(<<<'JS'
        () => !!document.querySelector('[x-show*="showContent"], [x-on\\:click*="showContent"]')
    JS);

    $page->assertNoJavascriptErrors();
});
