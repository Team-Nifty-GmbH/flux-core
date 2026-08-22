<?php

use FluxErp\Actions\Order\UpdateLockedOrder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Paid;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderList::class)
        ->assertOk();
});

test('marking orders as paid dispatches one job per open order', function (): void {
    Queue::fake();

    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $orderData = [
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ];

    $open = Order::factory()->create($orderData + ['payment_state' => Open::class]);
    $alreadyPaid = Order::factory()->create($orderData + ['payment_state' => Paid::class]);

    Livewire::test(OrderList::class)
        ->set('selected', [$open->getKey(), $alreadyPaid->getKey()])
        ->call('markAsPaid')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('selected', []);

    // the order that is settled already is filtered out
    Queue::assertPushed(
        UpdateLockedOrder::class,
        fn (UpdateLockedOrder $job): bool => $job->getData('id') === $open->getKey()
            && $job->getData('payment_state') === Paid::class
    );
    Queue::assertPushed(UpdateLockedOrder::class, 1);
});

test('the dispatched job settles the order', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);

    $order = Order::factory()->create([
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'payment_state' => Open::class,
        'is_locked' => true,
    ]);

    UpdateLockedOrder::make([
        'id' => $order->getKey(),
        'payment_state' => Paid::class,
    ])
        ->validate()
        ->execute();

    expect($order->refresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('marking orders as paid does nothing without a selection', function (): void {
    Queue::fake();

    Livewire::test(OrderList::class)
        ->set('selected', [])
        ->call('markAsPaid')
        ->assertOk()
        ->assertHasNoErrors();

    Queue::assertNothingPushed();
});

function orderWithoutCoordinates(): Order
{
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);

    return Order::factory()->create([
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached(test()->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => test()->defaultLanguage->getKey(),
        'tenant_id' => test()->dbTenant->getKey(),
    ]);
}

test('does not count orders without coordinates while the map is closed', function (): void {
    orderWithoutCoordinates();

    $coordinateQueries = 0;
    DB::listen(function (QueryExecuted $query) use (&$coordinateQueries): void {
        if (str_contains($query->sql, 'latitude')) {
            $coordinateQueries++;
        }
    });

    Livewire::test(OrderList::class)
        ->assertSet('showMap', false)
        ->assertOk()
        ->assertDontSee('orders without coordinates');

    expect(0)->toBe($coordinateQueries);
});

test('counts orders without coordinates once the map is open', function (): void {
    orderWithoutCoordinates();

    Livewire::test(OrderList::class)
        ->set('showMap', true)
        ->assertOk()
        ->assertSee(__(':count orders without coordinates', ['count' => 1]));
});
