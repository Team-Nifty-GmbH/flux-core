<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;

test('excludes free text positions from total net price calculation', function (): void {
    $contact = Contact::factory()
        ->has(Address::factory()->state(['tenant_id' => $this->dbTenant->getKey()]))
        ->create(['tenant_id' => $this->dbTenant->getKey()]);

    $address = $contact->addresses()->first();

    $orderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'shipping_costs_net_price' => 0,
        'is_locked' => false,
    ]);

    // Free text group header with a stale total_net_price in DB
    $freeTextParent = OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
        'total_net_price' => 500,
        'total_base_net_price' => 500,
    ]);

    // Child positions under the free text group
    OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'parent_id' => $freeTextParent->getKey(),
        'total_net_price' => 300,
        'total_base_net_price' => 300,
    ]);

    OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'parent_id' => $freeTextParent->getKey(),
        'total_net_price' => 200,
        'total_base_net_price' => 200,
    ]);

    // A regular root position
    OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
        'total_net_price' => 100,
        'total_base_net_price' => 100,
    ]);

    $order->calculateTotalNetPrice();

    // 300 + 200 + 100 = 600 (free text parent's DB value of 500 must NOT be counted)
    expect((string) $order->total_net_price)->toBe('600')
        ->and((string) $order->total_base_net_price)->toBe('600');
});
