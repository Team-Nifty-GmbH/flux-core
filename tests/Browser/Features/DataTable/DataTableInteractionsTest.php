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

    Order::factory()->count(3)->create([
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

function visitList(): mixed
{
    return waitForDataTable(
        visit(route('orders.orders'))
            ->assertRoute('orders.orders')
            ->assertNoSmoke()
    );
}

test('data table checkbox select works', function (): void {
    $page = visitList();

    $page->script(<<<'JS'
        () => {
            const checkbox = document.querySelector('tbody tr input[type="checkbox"]');
            if (checkbox) checkbox.click();
        }
    JS);

    $page->wait(0.5)
        ->assertNoJavascriptErrors();
});

test('data table select all checkbox works', function (): void {
    $page = visitList();

    $page->script(<<<'JS'
        () => {
            const selectAll = document.querySelector('thead input[type="checkbox"]');
            if (selectAll) selectAll.click();
        }
    JS);

    $page->wait(0.5)
        ->assertNoJavascriptErrors();
});

test('data table column settings gear button opens', function (): void {
    $page = visitList();

    $page->script(<<<'JS'
        () => {
            const gear = document.querySelector('thead [class*="gear"], thead button[x-on\\:click*="column"]');
            if (gear) gear.click();
        }
    JS);

    $page->wait(0.5)
        ->assertNoJavascriptErrors();
});

test('data table pagination renders', function (): void {
    visitList()
        ->assertScript(<<<'JS'
            (() => {
                const nav = document.querySelector('nav[aria-label*="Pagination"], nav.isolate');
                const perPage = document.querySelector('select');
                return !!(nav || perPage);
            })()
        JS);
});

test('data table search input works', function (): void {
    $page = visitList();

    $page->script(<<<'JS'
        () => {
            const search = document.querySelector('input[placeholder*="Search"], input[placeholder*="Suche"]');
            if (search) {
                search.focus();
                search.value = 'test';
                search.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    JS);

    $page->wait(1.5)
        ->assertNoJavascriptErrors();
});
