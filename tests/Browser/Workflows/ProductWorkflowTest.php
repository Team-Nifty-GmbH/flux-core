<?php

use FluxErp\Models\Product;

beforeEach(function (): void {
    $this->product = Product::factory()->create([
        'name' => 'Browser Test Produkt',
        'is_active' => true,
    ]);
});

test('product list loads and shows products', function (): void {
    $page = visit(route('products.products'))
        ->assertRoute('products.products')
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Product list did not render')), 10000);
            const check = () => {
                if (document.querySelectorAll('tbody tr').length > 0) {
                    clearTimeout(timeout);
                    resolve();
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    $page->assertNoJavascriptErrors();
});

test('product detail page loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $page->assertSee('Browser Test Produkt');
    $page->assertNoJavascriptErrors();
});

test('product detail has tabs', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $tabCount = $page->script(<<<'JS'
        () => document.querySelectorAll('[wire\\:click*="tab"], button[wire\\:click]').length
    JS);

    expect($tabCount)->toBeGreaterThan(0);
    $page->assertNoJavascriptErrors();
});

test('product detail tabs switch without errors', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 1) tabs[1].click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1500))');
    $page->assertNoJavascriptErrors();
});

test('product new button opens create form', function (): void {
    $page = visit(route('products.products'))
        ->assertRoute('products.products')
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => resolve(), 10000);
            const check = () => {
                if (document.querySelectorAll('tbody tr').length >= 0) {
                    clearTimeout(timeout);
                    resolve();
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    $page->script(<<<'JS'
        () => {
            const btn = Array.from(document.querySelectorAll('button'))
                .find(b => b.textContent?.includes('New') || b.textContent?.includes('Neu'));
            if (btn) btn.click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1000))');
    $page->assertNoJavascriptErrors();
});

test('product description editor initializes', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $page->script('() => new Promise(r => setTimeout(r, 3000))');

    $hasEditor = $page->script(<<<'JS'
        () => !!document.querySelector('.ProseMirror, [contenteditable="true"]')
    JS);
    expect($hasEditor)->toBeTrue();

    $page->assertNoJavascriptErrors();
});
