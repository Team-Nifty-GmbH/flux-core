<?php

/**
 * The column labels are carried either by plain sticky or by the floating head,
 * never by both. Which one applies depends on whether anything above the table
 * turns into a scroll port, and only a real browser resolves that.
 */

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
            const labels = table.querySelector('thead').rows[0];

            let bound = false;
            for (let node = table.parentElement; node && node !== document.body; node = node.parentElement) {
                const s = getComputedStyle(node);
                if (s.overflowX !== 'visible' || s.overflowY !== 'visible') {
                    bound = true;
                    break;
                }
            }

            window.scrollTo(0, 600);
            await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));

            return {
                stickyIsBound: bound,
                transform: getComputedStyle(labels).transform,
                travel: labels.style.getPropertyValue('--flux-head-travel'),
                animationName: getComputedStyle(labels).animationName,
            };
        }
    JS);

    $data = is_array($result) ? ($result[0] ?? $result) : $result;

    // Where nothing binds sticky, the row must carry no transform at all. An
    // identity matrix would still make it the containing block of its own
    // sticky cells and push the labels into the middle of the table.
    if ($data['stickyIsBound'] === false) {
        expect($data['transform'])->toBe('none')
            ->and($data['animationName'])->toBe('none');
    } else {
        expect($data['travel'])->not->toBe('0px');
    }
});
