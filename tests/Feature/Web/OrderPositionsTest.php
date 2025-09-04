<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $address = Address::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $contact->id,
    ]);

    $priceList = PriceList::factory()->create();

    $currency = Currency::factory()->create([
        'is_default' => true,
    ]);

    $language = Language::factory()->create();

    $orderType = OrderType::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create([
            'is_default' => false,
        ]);

    $order = Order::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'language_id' => $language->id,
        'order_type_id' => $orderType->id,
        'payment_type_id' => $paymentType->id,
        'price_list_id' => $priceList->id,
        'currency_id' => $currency->id,
        'address_invoice_id' => $address->id,
        'address_delivery_id' => $address->id,
        'is_locked' => false,
    ]);

    OrderPosition::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'order_id' => $order->id,
    ]);
});

test('order positions no user', function (): void {
    $this->actingAsGuest();

    $this->get('/orders/order-positions/list')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('order positions page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('orders.order-positions.list.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/orders/order-positions/list')
        ->assertOk();
});

test('order positions without permission', function (): void {
    Permission::findOrCreate('orders.order-positions.list.get', 'web');

    $this->actingAs($this->user, 'web')->get('/orders/order-positions/list')
        ->assertForbidden();
});
