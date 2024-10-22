<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class OrderTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $addresses;

    private Collection $clients;

    private Collection $languages;

    private Collection $orderTypes;

    private Collection $paymentTypes;

    private Collection $priceLists;

    private Collection $orders;

    private array $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $this->clients = Client::factory()->count(2)->create();

        $contacts = Contact::factory()->count(2)->create([
            'client_id' => $this->clients[0]->id,
        ]);
        $this->addresses = Address::factory()->count(2)->create([
            'client_id' => $this->clients[0]->id,
            'contact_id' => $contacts[0]->id,
        ]);

        $this->priceLists = PriceList::factory()->count(2)->create();

        $currencies = Currency::factory()->count(2)->create();
        Currency::query()->first()->update(['is_default' => true]);

        $this->languages = Language::factory()->count(2)->create();

        $this->orderTypes = OrderType::factory()->count(2)->create([
            'client_id' => $this->clients[0]->id,
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $this->paymentTypes = PaymentType::factory()->count(2)->create([
            'client_id' => $this->clients[0]->id,
        ]);

        $priceLists = PriceList::factory()->count(2)->create();

        $addresses = Address::factory()->count(2)->create([
            'client_id' => $this->clients[0]->id,
            'contact_id' => $contacts->random()->id,
        ]);

        $this->orders = Order::factory()->count(3)->create([
            'client_id' => $this->clients[0]->id,
            'language_id' => $this->languages[0]->id,
            'order_type_id' => $this->orderTypes[0]->id,
            'payment_type_id' => $this->paymentTypes[0]->id,
            'price_list_id' => $priceLists[0]->id,
            'currency_id' => $currencies[0]->id,
            'address_invoice_id' => $addresses->random()->id,
            'address_delivery_id' => $addresses->random()->id,
            'is_locked' => false,
        ]);

        $this->user->clients()->attach($this->clients->pluck('id')->toArray());

        $this->permissions = [
            'show' => Permission::findOrCreate('api.orders.{id}.get'),
            'index' => Permission::findOrCreate('api.orders.get'),
            'create' => Permission::findOrCreate('api.orders.post'),
            'update' => Permission::findOrCreate('api.orders.put'),
            'delete' => Permission::findOrCreate('api.orders.{id}.delete'),
        ];
    }

    public function test_get_order()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/orders/' . $this->orders[0]->id);
        $response->assertStatus(200);

        $order = json_decode($response->getContent())->data;

        $this->assertEquals($this->orders[0]->id, $order->id);
        $this->assertEquals($this->orders[0]->client_id, $order->client_id);
        $this->assertEquals($this->orders[0]->language_id, $order->language_id);
        $this->assertEquals($this->orders[0]->order_type_id, $order->order_type_id);
        $this->assertEquals($this->orders[0]->payment_type_id, $order->payment_type_id);
        $this->assertEquals($this->orders[0]->is_locked, $order->is_locked);
    }

    public function test_get_order_order_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/orders/' . $this->orders[2]->id + 10000);
        $response->assertStatus(404);
    }

    public function test_get_orders()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/orders');
        $response->assertStatus(200);

        $orders = json_decode($response->getContent())->data;

        $this->assertEquals($this->orders[0]->id, $orders->data[0]->id);
        $this->assertEquals($this->orders[0]->client_id, $orders->data[0]->client_id);
        $this->assertEquals($this->orders[0]->language_id, $orders->data[0]->language_id);
        $this->assertEquals($this->orders[0]->order_type_id, $orders->data[0]->order_type_id);
        $this->assertEquals($this->orders[0]->payment_type_id, $orders->data[0]->payment_type_id);
        $this->assertEquals($this->orders[0]->is_locked, $orders->data[0]->is_locked);
    }

    public function test_create_order()
    {
        $order = [
            'address_invoice_id' => $this->addresses[0]->id,
            'address_delivery_id' => $this->addresses[1]->id,
            'client_id' => $this->clients[0]->id,
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
        $response->assertStatus(201);

        $responseOrder = json_decode($response->getContent())->data;
        $dbOrder = Order::query()
            ->whereKey($responseOrder->id)
            ->first();

        $this->assertEquals($order['client_id'], $dbOrder->client_id);
        $this->assertEquals($order['language_id'], $dbOrder->language_id);
        $this->assertEquals($order['order_type_id'], $dbOrder->order_type_id);
        $this->assertEquals($order['payment_type_id'], $dbOrder->payment_type_id);
        $this->assertEquals($order['payment_target'], $dbOrder->payment_target);
        $this->assertEquals($order['payment_discount_target'], $dbOrder->payment_discount_target);
        $this->assertEquals($order['payment_discount_percent'], $dbOrder->payment_discount_percent);
        $this->assertEquals($order['payment_reminder_days_1'], $dbOrder->payment_reminder_days_1);
        $this->assertEquals($order['payment_reminder_days_2'], $dbOrder->payment_reminder_days_2);
        $this->assertEquals($order['payment_reminder_days_3'], $dbOrder->payment_reminder_days_3);
        $this->assertEquals($order['payment_texts'], $dbOrder->payment_texts);
    }

    public function test_create_order_validation_fails()
    {
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
        $response->assertStatus(422);
    }

    public function test_create_order_with_address_delivery()
    {
        $order = [
            'address_invoice_id' => $this->addresses[0]->id,
            'address_delivery_id' => $this->addresses[1]->id,
            'client_id' => $this->clients[0]->id,
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
        $response->assertStatus(201);

        $responseOrder = json_decode($response->getContent())->data;
        $dbOrder = Order::query()
            ->whereKey($responseOrder->id)
            ->first();

        $this->assertEquals($order['address_delivery']['company'], $dbOrder->address_delivery['company']);
        $this->assertNull($dbOrder->address_delivery_id);
        $this->assertEquals($order['address_invoice_id'], $dbOrder->address_invoice_id);
        $this->assertEquals($order['client_id'], $dbOrder->client_id);
        $this->assertEquals($order['language_id'], $dbOrder->language_id);
        $this->assertEquals($order['order_type_id'], $dbOrder->order_type_id);
        $this->assertEquals($order['payment_type_id'], $dbOrder->payment_type_id);
        $this->assertEquals($order['price_list_id'], $dbOrder->price_list_id);
        $this->assertEquals($order['payment_target'], $dbOrder->payment_target);
        $this->assertEquals($order['payment_discount_target'], $dbOrder->payment_discount_target);
        $this->assertEquals($order['payment_discount_percent'], $dbOrder->payment_discount_percent);
        $this->assertEquals($order['payment_reminder_days_1'], $dbOrder->payment_reminder_days_1);
        $this->assertEquals($order['payment_reminder_days_2'], $dbOrder->payment_reminder_days_2);
        $this->assertEquals($order['payment_reminder_days_3'], $dbOrder->payment_reminder_days_3);
        $this->assertEquals($order['payment_texts'], $dbOrder->payment_texts);
        $this->assertEquals($order['order_date'], $dbOrder->order_date->format('Y-m-d'));
    }

    public function test_create_order_with_address_delivery_validation_fails()
    {
        $order = [
            'address_invoice_id' => $this->addresses[0]->id,
            'address_delivery_id' => $this->addresses[1]->id,
            'client_id' => $this->clients[0]->id,
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
        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('address_delivery.company');
    }

    public function test_update_order_address_delivery_validation_fails()
    {
        $order = [
            'id' => $this->orders[0]->id,
            'address_delivery' => [
                'zip' => -12345,
            ],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/orders', $order);
        $response->assertStatus(422);

        $response->assertJsonValidationErrorFor('address_delivery.zip');
    }

    public function test_update_order_address_delivery()
    {
        $order = [
            'id' => $this->orders[0]->id,
            'address_delivery' => [
                'company' => 'test company',
            ],
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/orders', $order);
        $response->assertStatus(200);

        $responseOrder = json_decode($response->getContent())->data;
        $dbOrder = Order::query()
            ->whereKey($responseOrder->id)
            ->first();

        $this->assertEquals($order['address_delivery']['company'], $dbOrder->address_delivery['company']);
        $this->assertNull($dbOrder->address_delivery_id);
        $this->assertEquals($this->orders[0]->address_invoice_id, $dbOrder->address_invoice_id);
        $this->assertEquals($this->orders[0]->client_id, $dbOrder->client_id);
        $this->assertEquals($this->orders[0]->language_id, $dbOrder->language_id);
        $this->assertEquals($this->orders[0]->order_type_id, $dbOrder->order_type_id);
        $this->assertEquals($this->orders[0]->payment_type_id, $dbOrder->payment_type_id);
        $this->assertEquals($this->orders[0]->payment_target, $dbOrder->payment_target);
        $this->assertEquals($this->orders[0]->payment_discount_target, $dbOrder->payment_discount_target);
        $this->assertEquals($this->orders[0]->payment_discount_percent, $dbOrder->payment_discount_percent);
        $this->assertEquals($this->orders[0]->payment_reminder_days_1, $dbOrder->payment_reminder_days_1);
        $this->assertEquals($this->orders[0]->payment_reminder_days_2, $dbOrder->payment_reminder_days_2);
        $this->assertEquals($this->orders[0]->payment_reminder_days_3, $dbOrder->payment_reminder_days_3);
        $this->assertEquals($this->orders[0]->payment_texts, $dbOrder->payment_texts);
        $this->assertEquals($this->orders[0]->order_date->format('Y-m-d'), $dbOrder->order_date->format('Y-m-d'));
    }

    public function test_update_order()
    {
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
        $response->assertStatus(200);

        $responseOrder = json_decode($response->getContent())->data;
        $dbOrder = Order::query()
            ->whereKey($responseOrder->id)
            ->first();

        $this->assertEquals($order['id'], $dbOrder->id);
        $this->assertEquals($order['language_id'], $dbOrder->language_id);
        $this->assertEquals($order['order_type_id'], $dbOrder->order_type_id);
        $this->assertEquals($order['payment_type_id'], $dbOrder->payment_type_id);
        $this->assertEquals($order['payment_target'], $dbOrder->payment_target);
        $this->assertEquals($order['payment_discount_target'], $dbOrder->payment_discount_target);
        $this->assertEquals($order['payment_discount_percent'], $dbOrder->payment_discount_percent);
        $this->assertEquals($order['payment_reminder_days_1'], $dbOrder->payment_reminder_days_1);
        $this->assertEquals($order['payment_reminder_days_2'], $dbOrder->payment_reminder_days_2);
        $this->assertEquals($order['payment_reminder_days_3'], $dbOrder->payment_reminder_days_3);
        $this->assertEquals($order['payment_texts'], $dbOrder->payment_texts);
    }

    public function test_update_order_with_additional_columns()
    {
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
        $response->assertStatus(200);

        $responseOrder = json_decode($response->getContent())->data;
        $dbOrder = Order::query()
            ->whereKey($responseOrder->id)
            ->first();

        $this->assertEquals($order['id'], $dbOrder->id);
        $this->assertEquals($order['language_id'], $dbOrder->language_id);
        $this->assertEquals($order['order_type_id'], $dbOrder->order_type_id);
        $this->assertEquals($order['payment_type_id'], $dbOrder->payment_type_id);
        $this->assertEquals($order['payment_target'], $dbOrder->payment_target);
        $this->assertEquals($order['payment_discount_target'], $dbOrder->payment_discount_target);
        $this->assertEquals($order['payment_discount_percent'], $dbOrder->payment_discount_percent);
        $this->assertEquals($order['payment_reminder_days_1'], $dbOrder->payment_reminder_days_1);
        $this->assertEquals($order['payment_reminder_days_2'], $dbOrder->payment_reminder_days_2);
        $this->assertEquals($order['payment_reminder_days_3'], $dbOrder->payment_reminder_days_3);
        $this->assertEquals($order['payment_texts'], $dbOrder->payment_texts);
    }

    public function test_update_order_validation_fails()
    {
        $order = [
            'client_id' => $this->clients[0]->id,
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
        $response->assertStatus(422);
    }

    public function test_delete_order()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/orders/' . $this->orders[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(Order::query()->whereKey($this->orders[0]->id)->exists());
    }

    public function test_delete_order_order_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/orders/' . ++$this->orders[2]->id);
        $response->assertStatus(404);
    }

    public function test_delete_order_order_is_locked()
    {
        $this->orders[1]->is_locked = true;
        $this->orders[1]->save();

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/orders/' . $this->orders[1]->id);
        $response->assertStatus(423);
    }
}
