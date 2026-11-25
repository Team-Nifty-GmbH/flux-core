<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\Related\Projects;
use FluxErp\Livewire\Widgets\ProjectMargin;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Project;
use Illuminate\Support\Number;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
    $this->orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $this->makeOrder = fn (): Order => Order::factory()->create([
        'address_invoice_id' => $this->address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => $this->orderType->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
    $this->project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
    ]);
});

test('a project knows its supplementary orders and an order its supplemented projects', function (): void {
    $order = ($this->makeOrder)();

    $this->project->supplementaryOrders()->attach($order->getKey());

    expect($this->project->supplementaryOrders()->pluck('orders.id')->all())->toBe([$order->getKey()])
        ->and($order->supplementedProjects()->pluck('projects.id')->all())->toBe([$this->project->getKey()]);
});

test('deleting an order removes it as supplementary order', function (): void {
    $order = ($this->makeOrder)();
    $this->project->supplementaryOrders()->attach($order->getKey());

    $order->forceDelete();

    expect($this->project->supplementaryOrders()->count())->toBe(0);
});

test('project costs count only on the main order, not on a supplementary order', function (): void {
    $mainOrder = ($this->makeOrder)();
    $supplementaryOrder = ($this->makeOrder)();
    Project::query()
        ->whereKey($this->project->getKey())
        ->update(['order_id' => $mainOrder->getKey(), 'total_cost' => 500]);
    $this->project->supplementaryOrders()->attach($supplementaryOrder->getKey());

    expect($mainOrder->refresh()->calculateMargin()->total_cost)->toEqual(500)
        ->and($supplementaryOrder->refresh()->calculateMargin()->total_cost)->toEqual(0);
});

test('the projects tab of an order lists projects it supplements', function (): void {
    $order = ($this->makeOrder)();
    $this->project->supplementaryOrders()->attach($order->getKey());

    $data = Livewire::test(Projects::class, ['orderId' => $order->getKey()])
        ->call('loadData')
        ->instance()
        ->getDataForTesting();

    expect(collect(data_get($data, 'data'))->pluck('id')->all())->toBe([$this->project->getKey()]);
});

test('project margin includes supplementary orders', function (): void {
    $mainOrder = ($this->makeOrder)();
    $supplementaryOrder = ($this->makeOrder)();
    Project::query()
        ->whereKey($this->project->getKey())
        ->update(['order_id' => $mainOrder->getKey()]);
    $this->project->supplementaryOrders()->attach($supplementaryOrder->getKey());
    Order::query()->whereKey($mainOrder->getKey())->update(['margin' => 100]);
    Order::query()->whereKey($supplementaryOrder->getKey())->update(['margin' => 50]);

    $sum = Livewire::test(ProjectMargin::class, ['projectId' => $this->project->getKey()])
        ->call('calculateSum')
        ->get('sum');

    expect($sum)->toStartWith(Number::format(150, 2));
});
