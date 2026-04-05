<?php

test('settings page loads without js errors', function (): void {
    visit(route('settings'))
        ->assertNoSmoke();
});

test('settings tree navigation works', function (): void {
    $page = visit(route('settings'))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const items = document.querySelectorAll('[wire\\:click*="component"]');
            if (items.length > 1) items[1].click();
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('settings mobile toggle works', function (): void {
    visit(route('settings'))
        ->assertNoSmoke()
        ->assertScript("!!document.querySelector('[x-show*=\"showContent\"], [x-on\\\\:click*=\"showContent\"]')");
});
