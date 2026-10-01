<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Project\Orders;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Project;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $this->makeOrder = fn (): Order => Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
    $this->mainOrder = ($this->makeOrder)();
    $this->project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'order_id' => $this->mainOrder->getKey(),
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Orders::class, ['projectId' => $this->project->getKey()])
        ->assertOk();
});

test('lists the main order and supplementary orders', function (): void {
    $supplementaryOrder = ($this->makeOrder)();
    ($this->makeOrder)();
    $this->project->supplementaryOrders()->attach($supplementaryOrder->getKey());

    $data = Livewire::test(Orders::class, ['projectId' => $this->project->getKey()])
        ->call('loadData')
        ->instance()
        ->getDataForTesting();

    expect(collect(data_get($data, 'data'))->pluck('id')->sort()->values()->all())
        ->toBe([$this->mainOrder->getKey(), $supplementaryOrder->getKey()]);
});

test('lists supplementary orders of a project without main order', function (): void {
    Project::query()
        ->whereKey($this->project->getKey())
        ->update(['order_id' => null]);
    $supplementaryOrder = ($this->makeOrder)();
    $this->project->supplementaryOrders()->attach($supplementaryOrder->getKey());

    $data = Livewire::test(Orders::class, ['projectId' => $this->project->getKey()])
        ->call('loadData')
        ->instance()
        ->getDataForTesting();

    expect(collect(data_get($data, 'data'))->pluck('id')->all())->toBe([$supplementaryOrder->getKey()]);
});

test('adds and removes a supplementary order', function (): void {
    $order = ($this->makeOrder)();

    Livewire::test(Orders::class, ['projectId' => $this->project->getKey()])
        ->set('supplementaryOrderId', $order->getKey())
        ->call('addSupplementaryOrder')
        ->assertReturned(true);

    expect($this->project->supplementaryOrders()->pluck('orders.id')->all())->toBe([$order->getKey()]);

    Livewire::test(Orders::class, ['projectId' => $this->project->getKey()])
        ->call('removeSupplementaryOrder', $order->getKey())
        ->assertReturned(true);

    expect($this->project->supplementaryOrders()->count())->toBe(0);
});

test('rejects the main order as supplementary order', function (): void {
    Livewire::test(Orders::class, ['projectId' => $this->project->getKey()])
        ->set('supplementaryOrderId', $this->mainOrder->getKey())
        ->call('addSupplementaryOrder')
        ->assertReturned(false);

    expect($this->project->supplementaryOrders()->count())->toBe(0);
});

test('offers no mass actions that would delete the orders', function (): void {
    $component = Livewire::test(Orders::class, ['projectId' => $this->project->getKey()]);

    expect($component->instance()->isSelectable)->toBeFalse();
});

test('the included view cannot be changed from the client', function (): void {
    Livewire::test(Orders::class, ['projectId' => $this->project->getKey()])
        ->set('includeBefore', 'flux::livewire.project.orders');
})->throws(Exception::class);

test('shows the main order above the list', function (): void {
    Livewire::test(Orders::class, ['projectId' => $this->project->getKey()])
        ->assertSee($this->mainOrder->getLabel());
});
