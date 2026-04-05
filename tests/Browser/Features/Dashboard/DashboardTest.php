<?php

test('dashboard loads without js errors', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();
});

test('dashboard renders grid container', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript("!!document.querySelector('.grid-stack')");
});

test('dashboard widget add button exists in edit mode', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript(<<<'JS'
            (() => {
                const buttons = document.querySelectorAll('button');
                return Array.from(buttons).some(b =>
                    b.querySelector('[class*="pencil"]') ||
                    b.getAttribute('x-on:click')?.includes('editGridMode')
                );
            })()
        JS);
});
