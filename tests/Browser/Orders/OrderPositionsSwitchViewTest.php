<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;

test('switch view from list back to table renders positions', function (): void {
    Warehouse::factory()->create(['is_default' => true]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $language = Language::factory()->create();
    $vatRate = VatRate::factory()->create();
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
    ]);
    $paymentType = PaymentType::factory()->create();
    $paymentType->tenants()->attach($this->dbTenant->getKey());
    $priceList = PriceList::factory()->create(['is_net' => true]);

    $order = Order::factory()->create([
        'currency_id' => Currency::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => $language->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'contact_id' => $contact->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'is_locked' => false,
    ]);

    $position = OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'SwitchViewTestPosition',
        'amount' => 1,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_gross_price' => 119,
        'total_net_price' => 100,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $order->calculatePrices()->save();

    $page = visit(route('orders.id', $order))
        ->assertNoSmoke();

    // Wait for the order positions table to load
    waitForDataTable($page);
    $page->assertSee('SwitchViewTestPosition');

    // Switch to list view
    $page->script("() => {
        const btn = document.querySelector('[wire\\\\:click=\"switchView(\\'list\\')\"]');
        if (!btn) throw new Error('List view button not found');
        btn.click();
    }");
    $page->script('() => new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error("Table did not disappear")), 5000);
        const check = () => {
            if (document.querySelector("[tall-datatable] table") === null) {
                clearTimeout(timeout);
                resolve();
            } else {
                setTimeout(check, 200);
            }
        };
        check();
    })');

    // Switch back to table view
    $page->script("() => {
        const btn = document.querySelector('[wire\\\\:click=\"switchView(\\'table\\')\"]');
        if (!btn) throw new Error('Table view button not found');
        btn.click();
    }");
    $page->script('() => new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error("Table did not reappear after switchView")), 5000);
        const check = () => {
            if (document.querySelector("[tall-datatable] table") !== null) {
                clearTimeout(timeout);
                resolve();
            } else {
                setTimeout(check, 200);
            }
        };
        check();
    })');
});
