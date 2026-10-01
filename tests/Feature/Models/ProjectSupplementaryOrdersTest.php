<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Project;

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
