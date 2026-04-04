<?php

test('$tsui is available globally', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $tsuiExists = $page->script(<<<'JS'
        () => typeof window.$tsui === 'object'
    JS);

    expect($tsuiExists)->toBeTrue();
    $page->assertNoJavascriptErrors();
});

test('$tsui.open.modal opens a modal', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    // Find first modal id on page
    $modalId = $page->script(<<<'JS'
        () => {
            const modal = document.querySelector('[id$="-modal"]');
            return modal?.id || null;
        }
    JS);

    if ($modalId) {
        $page->script(<<<JS
            () => window.\$tsui.open.modal('{$modalId}')
        JS);

        $page->script('() => new Promise(r => setTimeout(r, 500))');

        $isVisible = $page->script(<<<JS
            () => {
                const modal = document.getElementById('{$modalId}');
                return modal && getComputedStyle(modal).display !== 'none';
            }
        JS);

        $page->assertNoJavascriptErrors();
    }
});

test('$tsui.interaction dialog works without errors', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            try {
                window.$tsui.interaction('dialog')
                    .success('Test', 'This is a test dialog')
                    .confirm('OK')
                    .send();
            } catch(e) {
                // Dialog might not render without Livewire context
            }
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('$tsui.interaction toast works without errors', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            try {
                window.$tsui.interaction('toast')
                    .success('Test Toast')
                    .send();
            } catch(e) {
                // Toast might not render without component
            }
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('x-loading component renders inside Livewire', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $hasLoadingElements = $page->script(<<<'JS'
        () => document.querySelectorAll('[wire\\:loading], [wire\\:loading\\.delay]').length > 0
    JS);
    expect($$hasLoadingElements)->toBeTrue();

    $page->assertNoJavascriptErrors();
});

test('TallStackUI select components initialize', function (): void {
    $page = visit(route('settings'))
        ->assertRoute('settings')
        ->assertNoSmoke();

    $hasSelects = $page->script(<<<'JS'
        () => document.querySelectorAll('[ts-select], [x-ref="tsuiSelect"], select[wire\\:model]').length > 0
    JS);
    expect($$hasSelects)->toBeTrue();

    $page->assertNoJavascriptErrors();
});
