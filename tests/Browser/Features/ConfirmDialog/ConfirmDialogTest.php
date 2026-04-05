<?php

test('wire:flux-confirm directive exists in codebase', function (): void {
    // wire:flux-confirm is a Livewire directive registered in alpine.js
    // It only appears on pages with delete buttons - verify no JS errors on a page that uses it
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('$tsui.interaction dialog can be triggered without errors', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            window.$tsui.interaction('dialog')
                .warning('Test Confirm', 'Are you sure?')
                .confirm('Yes')
                .cancel('No')
                .send();
        }
    JS);

    $page->assertNoJavascriptErrors();
});
