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

test('the head is measured again after a block above the table shrinks', function (): void {
    $page = waitForDataTable(
        visit(route('orders.orders'))
            ->assertRoute('orders.orders')
    );

    $result = $page->script(<<<'JS'
        () => new Promise((resolve) => {
            const table = document.querySelector('[tall-datatable]');
            const labels = () => table.querySelector('table thead').rows[0];
            const filler = document.createElement('div');

            filler.id = 'shrinking-block';
            filler.style.height = (window.innerHeight * 3) + 'px';
            table.parentElement.insertBefore(filler, table);

            table.querySelectorAll('tbody tr').forEach((tr) => tr.style.height = '120px');

            const toBottom = () => new Promise((fertig) => {
                let letzte = -1;
                const schieben = () => {
                    window.scrollTo({
                        behavior: 'instant',
                        top: document.documentElement.scrollHeight,
                    });

                    if (window.scrollY === letzte) {
                        fertig();

                        return;
                    }

                    letzte = window.scrollY;
                    setTimeout(schieben, 150);
                };

                schieben();
            });
            const measured = {};

            const settle = (steps) => {
                const next = steps.shift();

                if (! next) {
                    const style = labels().getAttribute('style') || '';
                    const read = (name) => {
                        const match = style.match(
                            new RegExp('--flux-head-' + name + ':\\s*([-\\d.]+)')
                        );

                        return match ? parseFloat(match[1]) : null;
                    };

                    resolve({
                        maxScroll: document.documentElement.scrollHeight
                            - document.documentElement.clientHeight,
                        startAfter: read('start'),
                        startBefore: measured.startBefore,
                    });

                    return;
                }

                Promise.resolve(next()).then(
                    () => setTimeout(() => settle(steps), 700)
                );
            };

            settle([
                toBottom,
                () => {
                    const style = labels().getAttribute('style') || '';
                    const match = style.match(/--flux-head-start:\s*([-\d.]+)/);
                    measured.startBefore = match ? parseFloat(match[1]) : null;
                },
                () => filler.style.height = '0px',
            ]);
        })
    JS);

    $belege = json_encode($result);

    expect($result['startBefore'])->toBeGreaterThan(0, $belege)
        ->and($result['startAfter'])->not->toBe($result['startBefore'], $belege)
        ->and($result['startAfter'])->toBeLessThan($result['maxScroll'], $belege);
});

test('the measured range never reaches past what the page can scroll', function (): void {
    $page = waitForDataTable(
        visit(route('orders.orders'))
            ->assertRoute('orders.orders')
    );

    $result = $page->script(<<<'JS'
        () => new Promise((resolve) => {
            const table = document.querySelector('[tall-datatable]');
            const labels = () => table.querySelector('table thead').rows[0];
            const filler = document.createElement('div');

            filler.id = 'shrinking-block';
            filler.style.height = (window.innerHeight * 4) + 'px';
            table.parentElement.insertBefore(filler, table);

            table.querySelectorAll('tbody tr').forEach((tr) => tr.style.height = '120px');

            const settle = (steps) => {
                const next = steps.shift();

                if (! next) {
                    const style = labels().getAttribute('style') || '';
                    const read = (name) => {
                        const match = style.match(
                            new RegExp('--flux-head-' + name + ':\\s*([-\\d.]+)')
                        );

                        return match ? parseFloat(match[1]) : null;
                    };

                    resolve({
                        maxScroll: document.documentElement.scrollHeight
                            - document.documentElement.clientHeight,
                        start: read('start'),
                        travel: read('travel'),
                    });

                    return;
                }

                Promise.resolve(next()).then(
                    () => setTimeout(() => settle(steps), 700)
                );
            };

            settle([
                () => window.scrollTo({
                    behavior: 'instant',
                    top: document.documentElement.scrollHeight,
                }),
                () => filler.style.height = '0px',
            ]);
        })
    JS);

    expect($result['maxScroll'])->toBeGreaterThanOrEqual(0);

    if ($result['travel'] > 0) {
        expect($result['start'])->toBeLessThan($result['maxScroll']);
    } else {
        expect($result['start'])->toBeNull();
    }
});
