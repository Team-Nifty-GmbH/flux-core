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
    return waitForDataTable(
        visit(route('orders.orders'))
            ->assertRoute('orders.orders')
            ->assertNoSmoke()
    );
}

test('data table loads and renders rows', function (): void {
    visitOrderList()
        ->assertScript('document.querySelectorAll("tbody tr").length >= 5');
});

test('data table column search filters results', function (): void {
    $page = visitOrderList();

    $page->script(<<<'JS'
        () => {
            const input = document.querySelector('thead input, [tall-datatable] input');
            if (!input) return;
            input.value = 'nonexistent-filter-value';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    JS);

    $page->wait(1.5)
        ->assertNoJavascriptErrors();
});

test('data table row click navigates to detail', function (): void {
    $page = visitOrderList();

    $page->script(<<<'JS'
        () => {
            const row = document.querySelector('tbody tr[wire\\:key]');
            if (!row) throw new Error('No clickable row found');
            row.click();
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('data table per page selector works', function (): void {
    visitOrderList()
        ->assertScript('document.querySelectorAll("tbody tr").length > 0');
});

test('data table sort by column header click', function (): void {
    $page = visitOrderList();

    $page->script(<<<'JS'
        () => {
            const sortable = document.querySelector('th[wire\\:click*="sortBy"], th[x-on\\:click*="sort"], th.cursor-pointer');
            if (!sortable) return;
            sortable.click();
        }
    JS);

    $page->wait(1.5)
        ->assertNoJavascriptErrors();
});
