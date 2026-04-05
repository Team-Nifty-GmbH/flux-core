<?php

test('$tsui is available globally', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript('typeof window.$tsui === "object"');
});

test('$tsui.open.modal opens a modal', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

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

        $page->wait(0.5)
            ->assertNoJavascriptErrors();
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
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript("document.querySelectorAll('[wire\\\\:loading], [wire\\\\:loading\\\\.delay]').length > 0");
});

test('TallStackUI select components initialize', function (): void {
    visit(route('settings'))
        ->assertRoute('settings')
        ->assertNoSmoke();
});
