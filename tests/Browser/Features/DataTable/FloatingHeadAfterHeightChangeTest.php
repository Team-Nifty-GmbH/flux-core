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

    Order::factory()->count(15)->create([
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

test('the labels keep carrying after a block above the table shrinks', function (): void {
    $page = waitForDataTable(
        visit(route('orders.orders'))
            ->assertRoute('orders.orders')
    );

    $result = $page->script(<<<'JS'
        () => new Promise((resolve) => {
            const table = document.querySelector('[tall-datatable]');
            const labels = table.querySelector('table thead').rows[0];
            const filler = document.createElement('div');

            filler.id = 'shrinking-block';
            filler.style.height = (window.innerHeight * 3) + 'px';
            table.parentElement.insertBefore(filler, table);

            table.querySelectorAll('tbody tr').forEach((tr) => tr.style.height = '120px');

            const toBottom = () => window.scrollTo(0, document.documentElement.scrollHeight);

            const settle = (steps) => {
                const next = steps.shift();

                if (! next) {
                    resolve({
                        top: Math.round(labels.getBoundingClientRect().top),
                        maxScroll: document.documentElement.scrollHeight
                            - document.documentElement.clientHeight,
                        scrollY: Math.round(window.scrollY),
                    });

                    return;
                }

                next();
                setTimeout(() => settle(steps), 700);
            };

            settle([
                toBottom,
                () => filler.style.height = window.innerHeight + 'px',
                toBottom,
            ]);
        })
    JS);

    expect($result['maxScroll'])->toBeGreaterThan(0)
        ->and($result['top'])->toBeGreaterThanOrEqual(0)
        ->and($result['top'])->toBeLessThan(200);
});
