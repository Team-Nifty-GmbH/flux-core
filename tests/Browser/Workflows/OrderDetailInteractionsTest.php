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

    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'company' => 'Order Detail Test GmbH',
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $priceList = PriceList::factory()->create();
    $currency = Currency::factory()->create();
    $vatRate = VatRate::factory()->create();

    $this->order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => false,
    ]);

    OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'name' => 'Position Eins',
        'amount' => 3,
        'unit_net_price' => 50,
        'unit_gross_price' => 59.50,
        'total_net_price' => 150,
        'total_gross_price' => 178.50,
    ]);
});

test('order shows order number in header', function (): void {
    visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke()
        ->assertSee($this->order->order_number)
        ->assertNoJavascriptErrors();
});

test('order shows contact company', function (): void {
    visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke()
        ->assertSee('Order Detail Test GmbH')
        ->assertNoJavascriptErrors();
});

test('order shows position name', function (): void {
    visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke()
        ->assertSee('Position Eins')
        ->assertNoJavascriptErrors();
});

test('order has money formatted totals', function (): void {
    $page = visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke();

    $page->assertScript(<<<'JS'
        document.querySelectorAll('[x-html*="$nuxbe.format.money"]').length > 0
    JS);

    $page->assertNoJavascriptErrors();
});

test('order positions tab shows data table', function (): void {
    visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]')
        ->assertNoJavascriptErrors();
});

test('order comments tab loads without errors', function (): void {
    $page = visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            for (const tab of tabs) {
                if (tab.textContent?.includes('Comment') || tab.textContent?.includes('Kommentar')) {
                    tab.click();
                    return;
                }
            }
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('order attachments tab loads without errors', function (): void {
    $page = visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            for (const tab of tabs) {
                if (tab.textContent?.includes('Attachment') || tab.textContent?.includes('Anhäng')) {
                    tab.click();
                    return;
                }
            }
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('order activities tab loads without errors', function (): void {
    $page = visit(route('orders.id', ['id' => $this->order->getKey()]))
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

test('order communication tab loads without errors', function (): void {
    $page = visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            for (const tab of tabs) {
                if (tab.textContent?.includes('Communication') || tab.textContent?.includes('Kommunikation')) {
                    tab.click();
                    return;
                }
            }
        }
    JS);

    $page->assertNoJavascriptErrors();
});

test('order has save button', function (): void {
    $page = visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke();

    $page->assertScript(<<<'JS'
        !!Array.from(document.querySelectorAll('button')).find(b =>
            b.textContent?.includes('Save') || b.textContent?.includes('Speichern')
        )
    JS);
});

test('order detail page has interactive elements', function (): void {
    $page = visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke();

    $page->assertScript(<<<'JS'
        document.querySelectorAll('button, [wire\\:click], [x-on\\:click]').length > 5
    JS);
});

test('order select dropdowns are present', function (): void {
    $page = visit(route('orders.id', ['id' => $this->order->getKey()]))
        ->assertNoSmoke();

    $page->assertScript(<<<'JS'
        document.querySelectorAll('[wire\\:model*="contact_id"], [wire\\:model*="payment_type_id"]').length > 0
            || document.querySelectorAll('[ts-select], select[wire\\:model]').length > 0
    JS);
});

test('order position list page loads', function (): void {
    visit(route('orders.order-positions'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]')
        ->assertNoJavascriptErrors();
});
