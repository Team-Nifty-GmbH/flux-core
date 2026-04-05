<?php

test('global search bar exists and is focusable', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript(<<<'JS'
            (() => {
                const input = document.querySelector('input[placeholder*="Search"], input[placeholder*="Suche"]');
                if (input) { input.focus(); return true; }
                return false;
            })()
        JS);
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

    $page->wait(1.5)
        ->assertNoJavascriptErrors();
});
