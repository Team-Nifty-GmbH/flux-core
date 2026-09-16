<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;

const HEAD_HELPERS = <<<'JS'
    const table = document.querySelector('[tall-datatable]');
    const labels = () => table.querySelector('table thead').rows[0];

    const read = (name) => {
        const match = (labels().getAttribute('style') || '').match(
            new RegExp('--flux-head-' + name + ':\\s*([-\\d.]+)')
        );

        return match ? parseFloat(match[1]) : null;
    };

    const maxScroll = () => document.documentElement.scrollHeight
        - document.documentElement.clientHeight;

    const toBottom = () => new Promise((done) => {
        let previous = -1;

        const push = () => {
            window.scrollTo({
                behavior: 'instant',
                top: document.documentElement.scrollHeight,
            });

            if (window.scrollY === previous) {
                done();

                return;
            }

            previous = window.scrollY;
            setTimeout(push, 150);
        };

        push();
    });

    const settle = (steps, resolve) => {
        const next = steps.shift();

        if (! next) {
            resolve();

            return;
        }

        Promise.resolve(next()).then(() => setTimeout(() => settle(steps, resolve), 700));
    };
JS;

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

    $helpers = HEAD_HELPERS;

    $result = $page->script(<<<JS
        () => new Promise((resolve) => {
            {$helpers}

            const filler = document.createElement('div');

            filler.id = 'shrinking-block';
            filler.style.height = (window.innerHeight * 3) + 'px';
            table.parentElement.insertBefore(filler, table);

            table.querySelectorAll('tbody tr').forEach((tr) => tr.style.height = '120px');

            const measured = {};

            settle(
                [
                    toBottom,
                    () => measured.startBefore = read('start'),
                    () => new Promise((done) => {
                        filler.style.height = '0px';

                        setTimeout(() => {
                            measured.startAfter = read('start');
                            measured.maxScroll = maxScroll();
                            done();
                        }, 200);
                    }),
                ],
                () => resolve(measured)
            );
        })
    JS);

    $evidence = json_encode($result);

    expect($result['startBefore'])->toBeGreaterThan(0, $evidence)
        ->and($result['startAfter'])->not->toBe($result['startBefore'], $evidence)
        ->and($result['startAfter'])->toBeLessThan($result['maxScroll'], $evidence);
});

test('the measured range never reaches past what the page can scroll', function (): void {
    $page = waitForDataTable(
        visit(route('orders.orders'))
            ->assertRoute('orders.orders')
    );

    $helpers = HEAD_HELPERS;

    $result = $page->script(<<<JS
        () => new Promise((resolve) => {
            {$helpers}

            const filler = document.createElement('div');

            filler.id = 'shrinking-block';
            filler.style.height = (window.innerHeight * 4) + 'px';
            table.parentElement.insertBefore(filler, table);

            table.querySelectorAll('tbody tr').forEach((tr) => tr.style.height = '120px');

            settle(
                [
                    toBottom,
                    () => filler.style.height = '0px',
                ],
                () => resolve({
                    maxScroll: maxScroll(),
                    start: read('start'),
                    travel: read('travel'),
                })
            );
        })
    JS);

    $evidence = json_encode($result);

    expect($result['maxScroll'])->toBeGreaterThanOrEqual(0, $evidence);

    if ($result['travel'] > 0) {
        expect($result['start'])->toBeLessThan($result['maxScroll'], $evidence);
    } else {
        expect($result['start'])->toBeNull($evidence);
    }
});

test('the head never travels further than the page can scroll', function (): void {
    $page = waitForDataTable(
        visit(route('orders.orders'))
            ->assertRoute('orders.orders')
    );

    $helpers = HEAD_HELPERS;

    $result = $page->script(<<<JS
        () => new Promise((resolve) => {
            {$helpers}

            table.querySelectorAll('tbody tr').forEach((tr) => tr.style.height = '400px');

            settle(
                [toBottom],
                () => resolve({
                    end: read('end'),
                    maxScroll: maxScroll(),
                    travel: read('travel'),
                })
            );
        })
    JS);

    $evidence = json_encode($result);

    expect($result['maxScroll'])->toBeGreaterThan(0, $evidence);

    if ($result['travel'] > 0) {
        expect($result['end'])->toBeLessThanOrEqual($result['maxScroll'], $evidence);
    }
});
