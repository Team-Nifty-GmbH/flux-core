<?php

test('profile page loads without js errors', function (): void {
    visit(route('my-profile'))
        ->assertNoSmoke();
});

test('profile page has tabs', function (): void {
    visit(route('my-profile'))
        ->assertNoSmoke()
        ->assertScript("document.querySelectorAll('[wire\\\\:click*=\"tab\"], [x-on\\\\:click*=\"tab\"], button[wire\\\\:click]').length > 0");
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
