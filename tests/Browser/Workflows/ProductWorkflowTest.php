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

    waitForDataTable($page)
        ->assertNoJavascriptErrors();
});

test('product detail page loads', function (): void {
    visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke()
        ->assertSee('Browser Test Produkt');
});

test('product detail has tabs', function (): void {
    visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke()
        ->assertScript("document.querySelectorAll('[wire\\\\:click*=\"tab\"], button[wire\\\\:click]').length > 0");
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

    $page->wait(1.5)
        ->assertNoJavascriptErrors();
});

test('product new button opens create form', function (): void {
    $page = visit(route('products.products'))
        ->assertRoute('products.products')
        ->assertNoSmoke();

    waitForDataTable($page);
    clickCreateButton($page);

    $page->wait(1)
        ->assertNoJavascriptErrors();
});

test('product description editor initializes', function (): void {
    visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke()
        ->wait(3)
        ->assertScript('!!document.querySelector(".ProseMirror, [contenteditable=\\"true\\"]")');
});
