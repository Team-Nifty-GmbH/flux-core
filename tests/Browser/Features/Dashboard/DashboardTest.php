<?php

test('dashboard loads without js errors', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('dashboard renders grid container', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $hasGridStack = $page->script(<<<'JS'
        () => !!document.querySelector('.grid-stack')
    JS);
    expect($$hasGridStack)->toBeTrue();

    expect($hasGridStack)->toBeTrue();
    $page->assertNoJavascriptErrors();
});

test('dashboard widget add button exists in edit mode', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $hasEditButton = $page->script(<<<'JS'
        () => {
            const buttons = document.querySelectorAll('button');
            return Array.from(buttons).some(b =>
                b.querySelector('[class*="pencil"]') ||
                b.getAttribute('x-on:click')?.includes('editGridMode')
            );
        }
    JS);
    expect($$hasEditButton)->toBeTrue();

    expect($hasEditButton)->toBeTrue();
    $page->assertNoJavascriptErrors();
});
