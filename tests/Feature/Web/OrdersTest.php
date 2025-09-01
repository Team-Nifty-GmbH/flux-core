<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
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

    $this->order = Order::factory()->create([
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
});

test('orders id no user', function (): void {
    $this->get('/orders/' . $this->order->id)
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('orders id order not found', function (): void {
    $this->order->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/orders/' . $this->order->id)
        ->assertStatus(404);
});

test('orders id page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/orders/' . $this->order->id)
        ->assertStatus(200);
});

test('orders id without permission', function (): void {
    Permission::findOrCreate('orders.{id}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/orders/' . $this->order->id)
        ->assertStatus(403);
});

test('orders no user', function (): void {
    $this->get('/orders/list')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('orders page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('orders.list.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/orders/list')
        ->assertStatus(200);
});

test('orders without permission', function (): void {
    Permission::findOrCreate('orders.list.get', 'web');

    $this->actingAs($this->user, 'web')->get('/orders/list')
        ->assertStatus(403);
});
