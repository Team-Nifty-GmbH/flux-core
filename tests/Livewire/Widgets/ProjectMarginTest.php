<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Widgets\ProjectMargin;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Project;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProjectMargin::class)
        ->assertOk();
});

test('calculates margin from order and related orders', function (): void {
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $address = Address::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $contact->id,
    ]);

    $orderType = OrderType::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $parentOrder = Order::factory()->create([
        'client_id' => $this->dbClient->id,
        'order_type_id' => $orderType->id,
        'currency_id' => Currency::default()->id,
        'payment_type_id' => PaymentType::default()->id,
        'price_list_id' => PriceList::default()->id,
        'address_invoice_id' => $address->id,
        'address_delivery_id' => $address->id,
        'margin' => 1000.00,
    ]);

    Order::factory()->create([
        'client_id' => $this->dbClient->id,
        'order_type_id' => $orderType->id,
        'currency_id' => Currency::default()->id,
        'payment_type_id' => PaymentType::default()->id,
        'price_list_id' => PriceList::default()->id,
        'parent_id' => $parentOrder->id,
        'address_invoice_id' => $address->id,
        'address_delivery_id' => $address->id,
        'margin' => 500.00,
    ]);

    Order::factory()->create([
        'client_id' => $this->dbClient->id,
        'order_type_id' => $orderType->id,
        'currency_id' => Currency::default()->id,
        'payment_type_id' => PaymentType::default()->id,
        'price_list_id' => PriceList::default()->id,
        'parent_id' => $parentOrder->id,
        'address_invoice_id' => $address->id,
        'address_delivery_id' => $address->id,
        'margin' => 250.00,
    ]);

    $project = Project::factory()->create([
        'client_id' => $this->dbClient->id,
        'order_id' => $parentOrder->id,
    ]);

    Livewire::test(ProjectMargin::class, ['projectId' => $project->id])
        ->assertOk()
        ->assertSet('sum', '1,750.00 ' . Currency::default()->symbol);
});

test('returns zero when project has no order', function (): void {
    $project = Project::factory()->create([
        'client_id' => $this->dbClient->id,
        'order_id' => null,
    ]);

    Livewire::test(ProjectMargin::class, ['projectId' => $project->id])
        ->assertOk()
        ->assertSet('sum', '0.00 ' . Currency::default()->symbol);
});
