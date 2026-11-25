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
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
        'is_hidden' => false,
    ]);
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

test('carries the labels of a short table while the page scrolls', function (): void {
    $page = waitForDataTable(
        visit(route('orders.orders'))
            ->assertRoute('orders.orders')
            ->assertNoSmoke()
    );

    $result = $page->script(<<<'JS'
        async () => {
            const root = document.querySelector('[tall-datatable]');
            const table = root.querySelector('table');
            const wrapper = table.parentElement;
            const labels = table.querySelector('thead').rows[0];
            const settle = () => new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));

            wrapper.style.width = '500px';
            table.style.width = '1600px';

            const filler = document.createElement('div');
            filler.style.height = '2500px';
            document.body.appendChild(filler);
            await settle();
            await new Promise(r => setTimeout(r, 400));

            const head = table.querySelector('thead').getBoundingClientRect();
            const body = table.querySelector('tbody').getBoundingClientRect();

            return {
                boundByWrapper: wrapper.scrollWidth > wrapper.clientWidth,
                fitsOnScreen: (body.bottom - head.top) <= window.innerHeight,
                pageScrolls: document.documentElement.scrollHeight > window.innerHeight,
                travel: labels.style.getPropertyValue('--flux-head-travel'),
            };
        }
    JS);

    $data = is_array($result) ? ($result[0] ?? $result) : $result;

    expect($data['boundByWrapper'])->toBeTrue()
        ->and($data['fitsOnScreen'])->toBeTrue()
        ->and($data['pageScrolls'])->toBeTrue()
        ->and($data['travel'])->not->toBe('0px');
});
