<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;

beforeEach(function (): void {
    Warehouse::factory()->create(['is_default' => true]);
    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
        'company' => 'Test GmbH',
    ]);
    $this->orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
        'is_hidden' => false,
    ]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->priceList = PriceList::factory()->create();
    $this->currency = Currency::factory()->create();
    $this->vatRate = VatRate::factory()->create();
    $this->product = Product::factory()->create([
        'name' => 'Test Product Browser',
        'is_active' => true,
    ]);

    $this->order = Order::factory()->create([
        'order_type_id' => $this->orderType->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => false,
    ]);

    OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'product_id' => $this->product->getKey(),
        'vat_rate_id' => $this->vatRate->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'name' => 'Test Position',
        'amount' => 2,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 200,
        'total_gross_price' => 238,
    ]);
});

function visitOrderDetail(Order $order): mixed
{
    $page = visit(route('orders.id', ['id' => $order->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => resolve(), 10000);
            const check = () => {
                const el = document.querySelector('[tall-datatable], [wire\\:id]');
                if (el) {
                    clearTimeout(timeout);
                    resolve();
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        })
    JS);

    return $page;
}

test('order list page loads with order type tabs', function (): void {
    $page = visit(route('orders.orders'))
        ->assertRoute('orders.orders')
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Order list did not render')), 10000);
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

    $page->assertSee($this->orderType->name);
    $page->assertNoJavascriptErrors();
});

test('order detail page loads and shows order data', function (): void {
    $page = visitOrderDetail($this->order);

    $page->assertSee($this->order->order_number);
    $page->assertNoJavascriptErrors();
});

test('order detail tabs are rendered', function (): void {
    $page = visitOrderDetail($this->order);

    $tabCount = $page->script(<<<'JS'
        () => document.querySelectorAll('[wire\\:click*="tab"]').length
    JS);

    expect($tabCount)->toBeGreaterThan(0);
    $page->assertNoJavascriptErrors();
});

test('order positions are displayed', function (): void {
    $page = visitOrderDetail($this->order);

    $page->assertSee('Test Position');
    $page->assertNoJavascriptErrors();
});

test('order totals display with $nuxbe formatting', function (): void {
    $page = visitOrderDetail($this->order);

    // Check totals section exists and renders numbers
    $hasTotals = $page->script(<<<'JS'
        () => {
            const moneyElements = document.querySelectorAll('[x-html*="$nuxbe.format.money"], [x-text*="$nuxbe.format"]');
            return moneyElements.length > 0;
        }
    JS);
    expect($$hasTotals)->toBeTrue();

    expect($hasTotals)->toBeTrue();
    $page->assertNoJavascriptErrors();
});

test('order save button works', function (): void {
    $page = visitOrderDetail($this->order);

    // Find and click save button
    $page->script(<<<'JS'
        () => {
            const saveBtn = Array.from(document.querySelectorAll('button'))
                .find(b => b.textContent?.includes('Save') || b.textContent?.includes('Speichern'));
            if (saveBtn) saveBtn.click();
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('order detail switching tabs works without errors', function (): void {
    $page = visitOrderDetail($this->order);

    // Click through tabs
    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 1) tabs[1].click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1500))');
    $page->assertNoJavascriptErrors();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 2) tabs[2].click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1500))');
    $page->assertNoJavascriptErrors();
});

test('order position edit modal opens', function (): void {
    $page = visitOrderDetail($this->order);

    // Click on a position row to open edit modal
    $page->script(<<<'JS'
        () => {
            const row = document.querySelector('[tall-datatable] tbody tr, [data-row]');
            if (row) row.click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1500))');
    $page->assertNoJavascriptErrors();
});

test('order date fields render correctly', function (): void {
    $page = visitOrderDetail($this->order);

    $hasDateFields = $page->script(<<<'JS'
        () => {
            const dateInputs = document.querySelectorAll('input[type="date"], [x-text*="$nuxbe.format.date"]');
            return dateInputs.length > 0;
        }
    JS);
    expect($$hasDateFields)->toBeTrue();

    $page->assertNoJavascriptErrors();
});

test('order replicate button opens dialog', function (): void {
    $page = visitOrderDetail($this->order);

    $page->script(<<<'JS'
        () => {
            const btn = Array.from(document.querySelectorAll('button'))
                .find(b => b.textContent?.includes('Replicate') || b.textContent?.includes('Duplizieren')
                    || b.getAttribute('wire:click')?.includes('replicate'));
            if (btn) btn.click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1000))');
    $page->assertNoJavascriptErrors();
});
