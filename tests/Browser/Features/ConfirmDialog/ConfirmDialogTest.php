<?php

test('wire:flux-confirm directive exists in codebase', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();
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
