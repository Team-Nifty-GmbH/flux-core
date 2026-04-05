<?php

use FluxErp\Models\Product;

beforeEach(function (): void {
    $this->product = Product::factory()->create([
        'name' => 'Produkt Detail Test',
        'product_number' => 'PDT-001',
        'is_active' => true,
    ]);
});

test('product shows product number', function (): void {
    visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke()
        ->assertSee('PDT-001');
});

test('product shows product name', function (): void {
    visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke()
        ->assertSee('Produkt Detail Test');
});

test('product has description editor', function (): void {
    visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke()
        ->assertScript('!!document.querySelector(".ProseMirror, [contenteditable=\\"true\\"]")');
});

test('product prices tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    clickTab($page, 'Price', 'Preis')
        ->assertNoJavascriptErrors();
});

test('product variants tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    clickTab($page, 'Variant')
        ->assertNoJavascriptErrors();
});

test('product media tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    clickTab($page, 'Media', 'Medien')
        ->assertNoJavascriptErrors();
});

test('product bundle tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    clickTab($page, 'Bundle')
        ->assertNoJavascriptErrors();
});

test('product cross-selling tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    clickTab($page, 'Cross')
        ->assertNoJavascriptErrors();
});

test('product activities tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    clickTab($page, 'Activit', 'Aktivität')
        ->assertNoJavascriptErrors();
});

test('product has save button', function (): void {
    visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke()
        ->assertScript(<<<'JS'
            !!Array.from(document.querySelectorAll('button')).find(b =>
                b.textContent?.includes('Save') || b.textContent?.includes('Speichern')
            )
        JS);
});

test('product serial numbers page loads', function (): void {
    visit(route('products.serial-numbers'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});
