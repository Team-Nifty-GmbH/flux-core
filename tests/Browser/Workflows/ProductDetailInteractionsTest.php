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
        ->assertSee('PDT-001')
        ->assertNoJavascriptErrors();
});

test('product shows product name', function (): void {
    visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke()
        ->assertSee('Produkt Detail Test')
        ->assertNoJavascriptErrors();
});

test('product has description editor', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $page->assertScript(<<<'JS'
        !!document.querySelector('.ProseMirror, [contenteditable="true"]')
    JS);
});

test('product prices tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            for (const tab of tabs) {
                if (tab.textContent?.includes('Price') || tab.textContent?.includes('Preis')) {
                    tab.click();
                    return;
                }
            }
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('product variants tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            for (const tab of tabs) {
                if (tab.textContent?.includes('Variant') || tab.textContent?.includes('Variant')) {
                    tab.click();
                    return;
                }
            }
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('product media tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            for (const tab of tabs) {
                if (tab.textContent?.includes('Media') || tab.textContent?.includes('Medien')) {
                    tab.click();
                    return;
                }
            }
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('product bundle tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            for (const tab of tabs) {
                if (tab.textContent?.includes('Bundle') || tab.textContent?.includes('Bundle')) {
                    tab.click();
                    return;
                }
            }
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('product cross-selling tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            for (const tab of tabs) {
                if (tab.textContent?.includes('Cross') || tab.textContent?.includes('Cross')) {
                    tab.click();
                    return;
                }
            }
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('product activities tab loads', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            for (const tab of tabs) {
                if (tab.textContent?.includes('Activit') || tab.textContent?.includes('Aktivität')) {
                    tab.click();
                    return;
                }
            }
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('product has save button', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    $page->assertScript(<<<'JS'
        !!Array.from(document.querySelectorAll('button')).find(b =>
            b.textContent?.includes('Save') || b.textContent?.includes('Speichern')
        )
    JS);
});

test('product serial numbers page loads', function (): void {
    visit(route('products.serial-numbers'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]')
        ->assertNoJavascriptErrors();
});
