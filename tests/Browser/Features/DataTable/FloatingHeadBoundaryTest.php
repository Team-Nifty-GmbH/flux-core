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

    Order::factory()->count(30)->create([
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

it('leaves the labels alone where sticky can hold them', function (): void {
    $page = waitForDataTable(
        visit(route('orders.orders'))
            ->assertRoute('orders.orders')
            ->assertNoSmoke()
    );

    $result = $page->script(<<<'JS'
        async () => {
            const table = document.querySelector('[tall-datatable]');
            const body = table.querySelector('tbody');
            const labels = table.querySelector('thead').rows[0];
            const cell = [...labels.cells].find(el => getComputedStyle(el).position === 'sticky');

            window.scrollTo(0, 600);
            await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));

            const rows = body.getBoundingClientRect();

            return {
                rowsStillVisible: rows.bottom > 0 && rows.top < window.innerHeight,
                cellTop: Math.round(cell.getBoundingClientRect().top),
                rowTransform: getComputedStyle(labels).transform,
                rowAnimation: getComputedStyle(labels).animationName,
            };
        }
    JS);

    $data = is_array($result) ? ($result[0] ?? $result) : $result;

    expect($data['rowsStillVisible'])->toBeTrue()
        ->and($data['cellTop'])->toBeGreaterThanOrEqual(0)
        ->and($data['rowTransform'])->toBe('none')
        ->and($data['rowAnimation'])->toBe('none');
});

it('keeps carrying the labels of a table too wide to fit', function (): void {
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

            table.querySelectorAll('tbody tr').forEach((row) => {
                row.style.height = '120px';
            });

            const filler = document.createElement('div');
            filler.style.height = '10px';
            document.body.appendChild(filler);
            await settle();
            await new Promise(r => setTimeout(r, 400));

            const head = table.querySelector('thead').getBoundingClientRect();
            const body = table.querySelector('tbody').getBoundingClientRect();

            return {
                tallerThanScreen: (body.bottom - head.top) > window.innerHeight,
                boundByWrapper: wrapper.scrollWidth > wrapper.clientWidth,
                wrapperOverflowX: getComputedStyle(wrapper).overflowX,
                travel: labels.style.getPropertyValue('--flux-head-travel'),
            };
        }
    JS);

    $data = is_array($result) ? ($result[0] ?? $result) : $result;

    expect($data['tallerThanScreen'])->toBeTrue()
        ->and($data['boundByWrapper'])->toBeTrue()
        ->and($data['wrapperOverflowX'])->toBe('auto')
        ->and($data['travel'])->not->toBe('0px');
});
