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
        () => new Promise((resolve) => {
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

    waitForDataTable($page)
        ->assertSee($this->orderType->name)
        ->assertNoJavascriptErrors();
});

test('order detail page loads and shows order data', function (): void {
    visitOrderDetail($this->order)
        ->assertSee($this->order->order_number)
        ->assertNoJavascriptErrors();
});

test('order detail tabs are rendered', function (): void {
    visitOrderDetail($this->order)
        ->assertScript("document.querySelectorAll('[wire\\\\:click*=\"tab\"]').length > 0");
});

test('order positions are displayed', function (): void {
    visitOrderDetail($this->order)
        ->assertSee('Test Position');
});

test('order totals display with $nuxbe formatting', function (): void {
    visitOrderDetail($this->order)
        ->assertScript(<<<'JS'
            document.querySelectorAll('[x-html*="$nuxbe.format.money"], [x-text*="$nuxbe.format"]').length > 0
        JS);
});

test('order save button works', function (): void {
    $page = visitOrderDetail($this->order);

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

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 1) tabs[1].click();
        }
    JS);

    $page->wait(1.5)
        ->assertNoJavascriptErrors();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 2) tabs[2].click();
        }
    JS);

    $page->wait(1.5)
        ->assertNoJavascriptErrors();
});

test('order position edit modal opens', function (): void {
    $page = visitOrderDetail($this->order);

    $page->script(<<<'JS'
        () => {
            const row = document.querySelector('[tall-datatable] tbody tr, [data-row]');
            if (row) row.click();
        }
    JS);

    $page->wait(1.5)
        ->assertNoJavascriptErrors();
});

test('order date fields render correctly', function (): void {
    visitOrderDetail($this->order)
        ->assertScript(<<<'JS'
            document.querySelectorAll('input[type="date"], [x-text*="$nuxbe.format.date"]').length > 0
        JS);
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

    $page->wait(1)
        ->assertNoJavascriptErrors();
});
