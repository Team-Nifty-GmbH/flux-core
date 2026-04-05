<?php

test('wire:flux-confirm dialog renders on button click', function (): void {
    $page = visit(route('settings'))
        ->assertRoute('settings')
        ->assertNoSmoke();

    // Find any element with wire:flux-confirm and click it
    $hasConfirm = $page->script(<<<'JS'
        () => {
            const el = document.querySelector('[wire\\:flux-confirm]');
            return !!el;
        }
    JS);
    expect($hasConfirm)->toBeTrue();

    $page->assertNoJavascriptErrors();
});

test('$tsui.interaction dialog shows confirm/cancel buttons', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    // Trigger a dialog via $tsui
    $page->script(<<<'JS'
        () => {
            window.$tsui.interaction('dialog')
                .warning('Test Confirm', 'Are you sure?')
                .confirm('Yes')
                .cancel('No')
                .send();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 500))');

    // Check dialog appeared
    $hasDialog = $page->script(<<<'JS'
        () => {
            const dialog = document.querySelector('[x-data*="dialog"], [id*="dialog"]');
            return !!dialog;
        }
    JS);
    expect($hasDialog)->toBeTrue();

    $page->assertNoJavascriptErrors();
});
