<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\Related\DescendantOrders;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $currency = Currency::factory()->create();
    $language = Language::factory()->create();
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order]);
    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $priceList = PriceList::factory()->create();

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => $language->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'currency_id' => $currency->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'is_locked' => false,
    ]);

    Livewire::test(DescendantOrders::class, ['orderId' => $order->getKey()])
        ->assertOk();
});
