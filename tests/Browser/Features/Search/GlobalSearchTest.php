<?php

test('global search bar exists and is focusable', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $hasSearch = $page->script(<<<'JS'
        () => {
            const input = document.querySelector('input[placeholder*="Search"], input[placeholder*="Suche"]');
            if (input) {
                input.focus();
                return true;
            }
            return false;
        }
    JS);
    expect($$hasSearch)->toBeTrue();

    expect($hasSearch)->toBeTrue();
    $page->assertNoJavascriptErrors();
});

test('global search accepts input without errors', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const input = document.querySelector('input[placeholder*="Search"], input[placeholder*="Suche"]');
            if (!input) throw new Error('Search input not found');
            input.focus();
            input.value = 'test';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1500))');
    $page->assertNoJavascriptErrors();
});
