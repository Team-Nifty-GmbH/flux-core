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
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;

test('order position modal shows the activities of the edited position', function (): void {
    Warehouse::factory()->create(['is_default' => true]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
        'is_hidden' => false,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_locked' => false,
    ]);

    OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'vat_rate_id' => VatRate::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'name' => 'Position With Activities',
        'amount' => 1,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 100,
        'total_gross_price' => 119,
    ]);

    $page = visit(route('orders.id', ['id' => $order->getKey()]))
        ->assertNoSmoke();

    waitForDataTable($page);

    $page->script(<<<'JS'
        () => {
            const button = document.querySelector('[wire\\:click*="editOrderPosition"]');
            if (! button) throw new Error('Edit order position button not found');
            button.click();
        }
    JS);

    waitForCondition($page, <<<'JS'
        () => document.querySelector('#order-position-activities')?.offsetParent !== null
    JS);

    $page->script(<<<'JS'
        () => {
            const button = document.querySelector(
                '#order-position-activities [dusk="tallstackui_card_minimize"]',
            );
            if (! button) throw new Error('Activities card not found');
            button.click();
        }
    JS);

    $causer = json_encode($this->user->getLabel());

    waitForCondition($page, <<<JS
        () => document
            .querySelector('#order-position-activities')
            .textContent.includes({$causer})
    JS);

    $page->assertNoJavascriptErrors();
});
