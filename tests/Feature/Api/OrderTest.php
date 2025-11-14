<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->tenants = Tenant::factory()->count(2)->create();

    $contacts = Contact::factory()->count(2)->create([
        'tenant_id' => $this->tenants[0]->id,
    ]);
    $this->addresses = Address::factory()->count(2)->create([
        'tenant_id' => $this->tenants[0]->id,
        'contact_id' => $contacts[0]->id,
    ]);

    $this->priceLists = PriceList::factory()->count(2)->create();

    $currencies = Currency::factory()->count(2)->create();
    Currency::query()->first()->update(['is_default' => true]);

    $this->languages = Language::factory()->count(2)->create();

    $this->orderTypes = OrderType::factory()->count(2)->create([
        'tenant_id' => $this->tenants[0]->id,
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $this->paymentTypes = PaymentType::factory()
        ->count(2)
        ->hasAttached(factory: $this->tenants[0], relationship: 'tenants')
        ->create();

    $priceLists = PriceList::factory()->count(2)->create();

    $addresses = Address::factory()->count(2)->create([
        'tenant_id' => $this->tenants[0]->id,
        'contact_id' => $contacts->random()->id,
    ]);

    $this->orders = Order::factory()->count(3)->create([
        'tenant_id' => $this->tenants[0]->id,
        'language_id' => $this->languages[0]->id,
        'order_type_id' => $this->orderTypes[0]->id,
        'payment_type_id' => $this->paymentTypes[0]->id,
        'price_list_id' => $priceLists[0]->id,
        'currency_id' => $currencies[0]->id,
        'address_invoice_id' => $addresses->random()->id,
        'address_delivery_id' => $addresses->random()->id,
        'is_locked' => false,
    ]);

    $this->user->tenants()->attach($this->tenants->pluck('id')->toArray());

    $this->permissions = [
        'show' => Permission::findOrCreate('api.orders.{id}.get'),
        'index' => Permission::findOrCreate('api.orders.get'),
        'create' => Permission::findOrCreate('api.orders.post'),
        'update' => Permission::findOrCreate('api.orders.put'),
        'delete' => Permission::findOrCreate('api.orders.{id}.delete'),
    ];
});

test('create order', function (): void {
    $order = [
        'address_invoice_id' => $this->addresses[0]->id,
        'address_delivery_id' => $this->addresses[1]->id,
        'tenant_id' => $this->tenants[0]->id,
        'language_id' => $this->languages[0]->id,
        'order_type_id' => $this->orderTypes[0]->id,
        'payment_type_id' => $this->paymentTypes[0]->id,
        'price_list_id' => $this->priceLists[0]->id,
        'payment_target' => rand(10, 20),
        'payment_discount_target' => rand(3, 5),
        'payment_discount_percent' => rand(1, 10) / 100,
        'payment_reminder_days_1' => rand(1, 10),
        'payment_reminder_days_2' => rand(1, 10),
        'payment_reminder_days_3' => rand(1, 10),
        'payment_texts' => [Str::random(), Str::random(), Str::random()],
        'order_date' => date('Y-m-d', strtotime('+1 day')),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/orders', $order);
    $response->assertCreated();

    $responseOrder = json_decode($response->getContent())->data;
    $dbOrder = Order::query()
        ->whereKey($responseOrder->id)
        ->first();

    expect($dbOrder->tenant_id)->toEqual($order['tenant_id']);
    expect($dbOrder->language_id)->toEqual($order['language_id']);
    expect($dbOrder->order_type_id)->toEqual($order['order_type_id']);
    expect($dbOrder->payment_type_id)->toEqual($order['payment_type_id']);
    expect($dbOrder->payment_target)->toEqual($order['payment_target']);
    expect($dbOrder->payment_discount_target)->toEqual($order['payment_discount_target']);
    expect($dbOrder->payment_discount_percent)->toEqual($order['payment_discount_percent']);
    expect($dbOrder->payment_reminder_days_1)->toEqual($order['payment_reminder_days_1']);
    expect($dbOrder->payment_reminder_days_2)->toEqual($order['payment_reminder_days_2']);
    expect($dbOrder->payment_reminder_days_3)->toEqual($order['payment_reminder_days_3']);
    expect($dbOrder->payment_texts)->toEqual($order['payment_texts']);
});

test('create order validation fails', function (): void {
    $order = [
        'language_id' => $this->languages[0]->id,
        'order_type_id' => $this->orderTypes[0]->id,
        'payment_type_id' => $this->paymentTypes[0]->id,
        'payment_target' => rand(10, 20),
        'payment_discount_target' => rand(3, 5),
        'payment_discount_percent' => rand(1, 10) / 100,
        'payment_reminder_days_1' => rand(1, 10),
        'payment_reminder_days_2' => rand(1, 10),
        'payment_reminder_days_3' => rand(1, 10),
        'payment_texts' => [Str::random(300)],
        'order_date' => date('Y-m-d', strtotime('+1 day')),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/orders', $order);
    $response->assertUnprocessable();
});

test('create order with address delivery', function (): void {
    $order = [
        'address_invoice_id' => $this->addresses[0]->id,
        'address_delivery_id' => $this->addresses[1]->id,
        'tenant_id' => $this->tenants[0]->id,
        'language_id' => $this->languages[0]->id,
        'order_type_id' => $this->orderTypes[0]->id,
        'payment_type_id' => $this->paymentTypes[0]->id,
        'price_list_id' => $this->priceLists[0]->id,
        'address_delivery' => [
            'company' => 'test-company',
        ],
        'payment_target' => rand(10, 20),
        'payment_discount_target' => rand(3, 5),
        'payment_discount_percent' => rand(1, 10) / 100,
        'payment_reminder_days_1' => rand(1, 10),
        'payment_reminder_days_2' => rand(1, 10),
        'payment_reminder_days_3' => rand(1, 10),
        'payment_texts' => [Str::random(), Str::random(), Str::random()],
        'order_date' => date('Y-m-d', strtotime('+1 day')),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/orders', $order);
    $response->assertCreated();

    $responseOrder = json_decode($response->getContent())->data;
    $dbOrder = Order::query()
        ->whereKey($responseOrder->id)
        ->first();

    expect($dbOrder->address_delivery['company'])->toEqual($order['address_delivery']['company']);
    expect($dbOrder->address_delivery_id)->toBeNull();
    expect($dbOrder->address_invoice_id)->toEqual($order['address_invoice_id']);
    expect($dbOrder->tenant_id)->toEqual($order['tenant_id']);
    expect($dbOrder->language_id)->toEqual($order['language_id']);
    expect($dbOrder->order_type_id)->toEqual($order['order_type_id']);
    expect($dbOrder->payment_type_id)->toEqual($order['payment_type_id']);
    expect($dbOrder->price_list_id)->toEqual($order['price_list_id']);
    expect($dbOrder->payment_target)->toEqual($order['payment_target']);
    expect($dbOrder->payment_discount_target)->toEqual($order['payment_discount_target']);
    expect($dbOrder->payment_discount_percent)->toEqual($order['payment_discount_percent']);
    expect($dbOrder->payment_reminder_days_1)->toEqual($order['payment_reminder_days_1']);
    expect($dbOrder->payment_reminder_days_2)->toEqual($order['payment_reminder_days_2']);
    expect($dbOrder->payment_reminder_days_3)->toEqual($order['payment_reminder_days_3']);
    expect($dbOrder->payment_texts)->toEqual($order['payment_texts']);
    expect($dbOrder->order_date->format('Y-m-d'))->toEqual($order['order_date']);
});

test('create order with address delivery validation fails', function (): void {
    $order = [
        'address_invoice_id' => $this->addresses[0]->id,
        'address_delivery_id' => $this->addresses[1]->id,
        'tenant_id' => $this->tenants[0]->id,
        'language_id' => $this->languages[0]->id,
        'order_type_id' => $this->orderTypes[0]->id,
        'payment_type_id' => $this->paymentTypes[0]->id,
        'price_list_id' => $this->priceLists[0]->id,
        'address_delivery' => [
            'company' => 123,
        ],
        'payment_target' => rand(10, 20),
        'payment_discount_target' => rand(3, 5),
        'payment_discount_percent' => rand(1, 10) / 100,
        'payment_reminder_days_1' => rand(1, 10),
        'payment_reminder_days_2' => rand(1, 10),
        'payment_reminder_days_3' => rand(1, 10),
        'payment_texts' => [Str::random(), Str::random(), Str::random()],
        'order_date' => date('Y-m-d', strtotime('+1 day')),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/orders', $order);
    $response->assertUnprocessable();

    $response->assertJsonValidationErrorFor('address_delivery.company');
});

test('delete order', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/orders/' . $this->orders[0]->id);
    $response->assertNoContent();

    expect(Order::query()->whereKey($this->orders[0]->id)->exists())->toBeFalse();
});

test('delete order order is locked', function (): void {
    $this->orders[1]->is_locked = true;
    $this->orders[1]->save();

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/orders/' . $this->orders[1]->id);
    $response->assertStatus(423);
});

test('delete order order not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/orders/' . ++$this->orders[2]->id);
    $response->assertNotFound();
});

test('get order', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/orders/' . $this->orders[0]->id);
    $response->assertOk();

    $order = json_decode($response->getContent())->data;

    expect($order->id)->toEqual($this->orders[0]->id);
    expect($order->tenant_id)->toEqual($this->orders[0]->tenant_id);
    expect($order->language_id)->toEqual($this->orders[0]->language_id);
    expect($order->order_type_id)->toEqual($this->orders[0]->order_type_id);
    expect($order->payment_type_id)->toEqual($this->orders[0]->payment_type_id);
    expect($order->is_locked)->toEqual($this->orders[0]->is_locked);
});

test('get order order not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/orders/' . $this->orders[2]->id + 10000);
    $response->assertNotFound();
});

test('get orders', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/orders');
    $response->assertOk();

    $orders = json_decode($response->getContent())->data;

    expect($orders->data[0]->id)->toEqual($this->orders[0]->id);
    expect($orders->data[0]->tenant_id)->toEqual($this->orders[0]->tenant_id);
    expect($orders->data[0]->language_id)->toEqual($this->orders[0]->language_id);
    expect($orders->data[0]->order_type_id)->toEqual($this->orders[0]->order_type_id);
    expect($orders->data[0]->payment_type_id)->toEqual($this->orders[0]->payment_type_id);
    expect($orders->data[0]->is_locked)->toEqual($this->orders[0]->is_locked);
});

test('update order', function (): void {
    $order = [
        'id' => $this->orders[0]->id,
        'language_id' => $this->languages[0]->id,
        'order_type_id' => $this->orderTypes[0]->id,
        'payment_type_id' => $this->paymentTypes[0]->id,
        'payment_target' => rand(10, 20),
        'payment_discount_target' => rand(3, 5),
        'payment_discount_percent' => rand(1, 10) / 100,
        'payment_reminder_days_1' => rand(1, 10),
        'payment_reminder_days_2' => rand(1, 10),
        'payment_reminder_days_3' => rand(1, 10),
        'payment_texts' => [Str::random(300)],
        'order_date' => date('Y-m-d', strtotime('+1 day')),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/orders', $order);
    $response->assertOk();

    $responseOrder = json_decode($response->getContent())->data;
    $dbOrder = Order::query()
        ->whereKey($responseOrder->id)
        ->first();

    expect($dbOrder->id)->toEqual($order['id']);
    expect($dbOrder->language_id)->toEqual($order['language_id']);
    expect($dbOrder->order_type_id)->toEqual($order['order_type_id']);
    expect($dbOrder->payment_type_id)->toEqual($order['payment_type_id']);
    expect($dbOrder->payment_target)->toEqual($order['payment_target']);
    expect($dbOrder->payment_discount_target)->toEqual($order['payment_discount_target']);
    expect($dbOrder->payment_discount_percent)->toEqual($order['payment_discount_percent']);
    expect($dbOrder->payment_reminder_days_1)->toEqual($order['payment_reminder_days_1']);
    expect($dbOrder->payment_reminder_days_2)->toEqual($order['payment_reminder_days_2']);
    expect($dbOrder->payment_reminder_days_3)->toEqual($order['payment_reminder_days_3']);
    expect($dbOrder->payment_texts)->toEqual($order['payment_texts']);
});

test('update order address delivery', function (): void {
    $order = [
        'id' => $this->orders[0]->id,
        'address_delivery' => [
            'company' => 'test company',
        ],
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/orders', $order);
    $response->assertOk();

    $responseOrder = json_decode($response->getContent())->data;
    $dbOrder = Order::query()
        ->whereKey($responseOrder->id)
        ->first();

    expect($dbOrder->address_delivery['company'])->toEqual($order['address_delivery']['company']);
    expect($dbOrder->address_delivery_id)->toBeNull();
    expect($dbOrder->address_invoice_id)->toEqual($this->orders[0]->address_invoice_id);
    expect($dbOrder->tenant_id)->toEqual($this->orders[0]->tenant_id);
    expect($dbOrder->language_id)->toEqual($this->orders[0]->language_id);
    expect($dbOrder->order_type_id)->toEqual($this->orders[0]->order_type_id);
    expect($dbOrder->payment_type_id)->toEqual($this->orders[0]->payment_type_id);
    expect($dbOrder->payment_target)->toEqual($this->orders[0]->payment_target);
    expect($dbOrder->payment_discount_target)->toEqual($this->orders[0]->payment_discount_target);
    expect($dbOrder->payment_discount_percent)->toEqual($this->orders[0]->payment_discount_percent);
    expect($dbOrder->payment_reminder_days_1)->toEqual($this->orders[0]->payment_reminder_days_1);
    expect($dbOrder->payment_reminder_days_2)->toEqual($this->orders[0]->payment_reminder_days_2);
    expect($dbOrder->payment_reminder_days_3)->toEqual($this->orders[0]->payment_reminder_days_3);
    expect($dbOrder->payment_texts)->toEqual($this->orders[0]->payment_texts);
    expect($dbOrder->order_date->format('Y-m-d'))->toEqual($this->orders[0]->order_date->format('Y-m-d'));
});

test('update order address delivery validation fails', function (): void {
    $order = [
        'id' => $this->orders[0]->id,
        'address_delivery' => [
            'zip' => -12345,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/orders', $order);
    $response->assertUnprocessable();

    $response->assertJsonValidationErrorFor('address_delivery.zip');
});

test('update order validation fails', function (): void {
    $order = [
        'tenant_id' => $this->tenants[0]->id,
        'language_id' => $this->languages[0]->id,
        'order_type_id' => $this->orderTypes[0]->id,
        'payment_type_id' => $this->paymentTypes[0]->id,
        'payment_target' => rand(10, 20),
        'payment_discount_target' => rand(3, 5),
        'payment_discount_percent' => rand(1, 10) / 100,
        'payment_reminder_days_1' => rand(1, 10),
        'payment_reminder_days_2' => rand(1, 10),
        'payment_reminder_days_3' => rand(1, 10),
        'payment_texts' => [Str::random()],
        'order_date' => date('Y-m-d', strtotime('-1 day')),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/orders', $order);
    $response->assertUnprocessable();
});

test('update order with additional columns', function (): void {
    $additionalColumn = AdditionalColumn::factory()->create([
        'model_type' => Order::class,
    ]);

    $order = [
        'id' => $this->orders[0]->id,
        'language_id' => $this->languages[0]->id,
        'order_type_id' => $this->orderTypes[0]->id,
        'payment_type_id' => $this->paymentTypes[0]->id,
        'payment_target' => rand(10, 20),
        'payment_discount_target' => rand(3, 5),
        'payment_discount_percent' => rand(1, 10) / 100,
        'payment_reminder_days_1' => rand(1, 10),
        'payment_reminder_days_2' => rand(1, 10),
        'payment_reminder_days_3' => rand(1, 10),
        'payment_texts' => [Str::random(300)],
        'order_date' => date('Y-m-d', strtotime('-1 day')),
        $additionalColumn->name => 'Testvalue for this column',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/orders', $order);
    $response->assertOk();

    $responseOrder = json_decode($response->getContent())->data;
    $dbOrder = Order::query()
        ->whereKey($responseOrder->id)
        ->first();

    expect($dbOrder->id)->toEqual($order['id']);
    expect($dbOrder->language_id)->toEqual($order['language_id']);
    expect($dbOrder->order_type_id)->toEqual($order['order_type_id']);
    expect($dbOrder->payment_type_id)->toEqual($order['payment_type_id']);
    expect($dbOrder->payment_target)->toEqual($order['payment_target']);
    expect($dbOrder->payment_discount_target)->toEqual($order['payment_discount_target']);
    expect($dbOrder->payment_discount_percent)->toEqual($order['payment_discount_percent']);
    expect($dbOrder->payment_reminder_days_1)->toEqual($order['payment_reminder_days_1']);
    expect($dbOrder->payment_reminder_days_2)->toEqual($order['payment_reminder_days_2']);
    expect($dbOrder->payment_reminder_days_3)->toEqual($order['payment_reminder_days_3']);
    expect($dbOrder->payment_texts)->toEqual($order['payment_texts']);
});
