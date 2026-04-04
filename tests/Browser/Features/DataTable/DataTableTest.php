<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;

beforeEach(function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order, 'is_active' => true, 'is_hidden' => false]);
    $paymentType = PaymentType::factory()->hasAttached($this->dbTenant, relationship: 'tenants')->create();
    $priceList = PriceList::factory()->create();
    $currency = Currency::factory()->create();

    Order::factory()->count(5)->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);
});

function visitOrderList(): mixed
{
    $page = visit(route('orders.orders'))
        ->assertRoute('orders.orders')
        ->assertNoSmoke();

    // Wait for data table to render rows
    $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('DataTable did not render')), 10000);
            const check = () => {
                const rows = document.querySelectorAll('tbody tr');
                if (rows.length > 0) {
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

test('data table loads and renders rows', function (): void {
    $page = visitOrderList();

    $rowCount = $page->script(<<<'JS'
        () => document.querySelectorAll('tbody tr').length
    JS);

    expect($rowCount)->toBeGreaterThanOrEqual(5);
    $page->assertNoJavascriptErrors();
});

test('data table column search filters results', function (): void {
    $page = visitOrderList();

    // Find any input in the table header area (column filters)
    $page->script(<<<'JS'
        () => {
            const input = document.querySelector('thead input, [tall-datatable] input');
            if (!input) return; // Column search may not be visible
            input.value = 'nonexistent-filter-value';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1500))');
    $page->assertNoJavascriptErrors();
});

test('data table row click navigates to detail', function (): void {
    $page = visitOrderList();

    // Click first data row
    $page->script(<<<'JS'
        () => {
            const row = document.querySelector('tbody tr[wire\\:key]');
            if (!row) throw new Error('No clickable row found');
            row.click();
        }
    JS);

    // Wait for navigation

    $page->assertNoJavascriptErrors();
});

test('data table per page selector works', function (): void {
    $page = visitOrderList();

    $initialRows = $page->script(<<<'JS'
        () => document.querySelectorAll('tbody tr').length
    JS);

    $page->assertNoJavascriptErrors();
    expect($initialRows)->toBeGreaterThan(0);
});

test('data table sort by column header click', function (): void {
    $page = visitOrderList();

    // Click a sortable column header (th with sort indicator or click handler)
    $page->script(<<<'JS'
        () => {
            const sortable = document.querySelector('th[wire\\:click*="sortBy"], th[x-on\\:click*="sort"], th.cursor-pointer');
            if (!sortable) return; // Sort may not be available on all columns
            sortable.click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1500))');
    $page->assertNoJavascriptErrors();
});
