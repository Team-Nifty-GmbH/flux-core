<?php

test('profile page loads without js errors', function (): void {
    visit(route('my-profile'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('profile page has tabs', function (): void {
    $page = visit(route('my-profile'))
        ->assertNoSmoke();

    $tabCount = $page->script(<<<'JS'
        () => document.querySelectorAll('[wire\\:click*="tab"], [x-on\\:click*="tab"], button[wire\\:click]').length
    JS);

    expect($tabCount)->toBeGreaterThan(0);
    $page->assertNoJavascriptErrors();
});

test('profile tabs switch without errors', function (): void {
    $page = visit(route('my-profile'))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 1) tabs[1].click();
        }
    JS);

    $page->assertNoJavascriptErrors();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 2) tabs[2].click();
        }
    JS);

    $page->assertNoJavascriptErrors();
});
