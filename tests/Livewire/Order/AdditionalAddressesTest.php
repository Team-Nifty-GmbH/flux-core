<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\AdditionalAddresses;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

beforeEach(function (): void {
    $currency = Currency::factory()->create([
        'is_default' => true,
    ]);
    $contact = Contact::factory()->create();
    $priceList = PriceList::factory()->create([
        'is_default' => true,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create([
            'is_default' => true,
        ]);

    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order->value,
    ]);

    $address = Address::factory()->create([
        'contact_id' => $contact->id,
        'is_delivery_address' => true,
        'is_invoice_address' => true,
        'is_main_address' => true,
    ]);

    $this->order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $currency->id,
        'address_invoice_id' => $address->id,
        'price_list_id' => $priceList->id,
        'payment_type_id' => $paymentType->id,
        'order_type_id' => $orderType->id,
    ]);
});

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(AdditionalAddresses::class, ['orderId' => $this->order->id])
        ->assertOk();
});
