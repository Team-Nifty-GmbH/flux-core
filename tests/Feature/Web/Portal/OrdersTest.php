<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;

beforeEach(function (): void {
    $priceList = PriceList::factory()->create([
        'is_default' => true,
    ]);

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
            'is_default' => true,
            'is_active' => true,
            'is_sales' => true,
        ]);

    $this->order = Order::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'language_id' => $language->id,
        'order_type_id' => $orderType->id,
        'payment_type_id' => $paymentType->id,
        'price_list_id' => $priceList->id,
        'currency_id' => $currency->id,
        'address_invoice_id' => $this->address->id,
        'address_delivery_id' => $this->address->id,
        'is_locked' => true,
    ]);
});

test('portal orders id no user', function (): void {
    $this->actingAsGuest();

    $this->get(route('portal.orders.id', ['id' => $this->order->id]))
        ->assertFound()
        ->assertRedirect(config('flux.portal_domain') . '/login');
});

test('portal orders id order not contact id', function (): void {
    $this->order->update(['contact_id' => null]);

    $this->address->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.orders.id', ['id' => $this->order->id]))
        ->assertNotFound();
});

test('portal orders id order not found', function (): void {
    $this->order->delete();

    $this->address->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.orders.id', ['id' => $this->order->id]))
        ->assertNotFound();
});

test('portal orders id order not locked', function (): void {
    $this->order->update(['is_locked' => false, 'is_imported' => false]);

    $this->address->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.orders.id', ['id' => $this->order->id]))
        ->assertNotFound();
});

test('portal orders id page', function (): void {
    $this->address->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.orders.id', ['id' => $this->order->id]))
        ->assertOk();
});

test('portal orders id without permission', function (): void {
    Permission::findOrCreate('orders.{id}.get', 'address');

    $this->actingAs($this->address, 'address')->get(route('portal.orders.id', ['id' => $this->order->id]))
        ->assertForbidden();
});

test('portal orders no user', function (): void {
    $this->actingAsGuest();

    $this->get(route('portal.orders'))
        ->assertFound()
        ->assertRedirect(config('flux.portal_domain') . '/login');
});

test('portal orders page', function (): void {
    $this->address->givePermissionTo(Permission::findOrCreate('orders.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.orders'))
        ->assertOk();
});

test('portal orders without permission', function (): void {
    Permission::findOrCreate('orders.get', 'address');

    $this->actingAs($this->address, 'address')->get(route('portal.orders'))
        ->assertForbidden();
});
