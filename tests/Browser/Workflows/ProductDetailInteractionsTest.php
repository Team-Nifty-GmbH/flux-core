<?php

use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;

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

test('variant edit modal shows product options without js errors', function (): void {
    $group = ProductOptionGroup::factory()->create(['name' => 'Color']);
    ProductOption::factory()->create([
        'product_option_group_id' => $group->getKey(),
        'name' => 'Red',
    ]);
    ProductOption::factory()->create([
        'product_option_group_id' => $group->getKey(),
        'name' => 'Blue',
    ]);

    $page = visit(route('products.id', ['id' => $this->product->getKey()]) . '?tab=product.variant-list');

    // Wait for the variant list component to load
    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Edit Variants button not found')), 15000);
            const check = () => {
                const btn = Array.from(document.querySelectorAll('button')).find(b =>
                    b.textContent?.includes('Edit Variants') || b.textContent?.includes('Varianten bearbeiten')
                );
                if (btn) {
                    clearTimeout(timeout);
                    resolve();
                } else {
                    setTimeout(check, 300);
                }
            };
            check();
        })
    JS);

    // Click the "Edit Variants" button to open the modal
    $page->script(<<<'JS'
        () => {
            const btn = Array.from(document.querySelectorAll('button')).find(b =>
                b.textContent?.includes('Edit Variants') || b.textContent?.includes('Varianten bearbeiten')
            );
            btn.click();
        }
    JS);

    $page->wait(2);

    // Click the product option group row in the modal to load its options
    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Product option group row not found')), 10000);
            const check = () => {
                const rows = document.querySelectorAll('tbody tr');
                for (const row of rows) {
                    if (row.textContent?.includes('Color')) {
                        row.click();
                        clearTimeout(timeout);
                        resolve();
                        return;
                    }
                }
                setTimeout(check, 200);
            };
            check();
        })
    JS);

    // Wait for product options to load and verify no JS errors
    $page->wait(2)
        ->assertNoJavascriptErrors();
});
