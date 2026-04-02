<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\ReplicateOrderPositionList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $priceList = PriceList::factory()->create();

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    Livewire::test(ReplicateOrderPositionList::class, ['orderId' => $order->getKey()])
        ->assertOk();
});
