<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Discount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class DiscountTest extends BaseSetup
{
    use WithFaker;

    private $discounts;

    private $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $address = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->getKey(),
        ]);

        $priceList = PriceList::factory()->create();

        $currency = Currency::factory()->create([
            'is_default' => true,
        ]);

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
            'order_type_id' => $orderType->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'price_list_id' => $priceList->getKey(),
            'currency_id' => $currency->getKey(),
            'address_invoice_id' => $address->getKey(),
            'is_locked' => false,
        ]);

        $this->orderPositions = OrderPosition::factory()->count(3)->create([
            'client_id' => $this->dbClient->getKey(),
            'order_id' => $order->getKey(),
        ]);

        $this->discounts = Discount::factory()->count(3)->create([
            'model_id' => $this->orderPositions->random()->getKey(),
            'model_type' => morph_alias(OrderPosition::class),
        ]);

        $this->permissions = [
            'index' => Permission::findOrCreate('api.discounts.get'),
            'show' => Permission::findOrCreate('api.discounts.{id}.get'),
            'create' => Permission::findOrCreate('api.discounts.post'),
            'update' => Permission::findOrCreate('api.discounts.put'),
            'delete' => Permission::findOrCreate('api.discounts.{id}.delete'),
        ];
    }

    public function test_create_discount_success(): void
    {
        $payload = [
            'name' => 'Frühbucher',
            'discount' => 0.15,
            'is_percentage' => true,
            'model_id' => $this->orderPositions->random()->getKey(),
            'model_type' => morph_alias(OrderPosition::class),
            'from' => '2025-06-01 00:00:00',
            'till' => '2025-12-31 23:59:59',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/discounts', $payload);
        $response->assertStatus(201);

        $data = $response->json('data');
        $this->assertDatabaseHas('discounts', [
            'id' => $data['id'],
            'name' => $payload['name'],
            'discount' => $payload['discount'],
            'is_percentage' => $payload['is_percentage'],
        ]);
    }

    public function test_create_percentage_over_one_fails(): void
    {
        $payload = [
            'discount' => 1.5,
            'is_percentage' => true,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $this->post('/api/discounts', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['discount']);
    }

    public function test_create_validation_fails(): void
    {
        $payload = [
            'name' => 'Test',
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $this->post('/api/discounts', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['discount', 'is_percentage']);
    }

    public function test_delete_discount_success(): void
    {
        $discount = $this->discounts->last();

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $this->delete('/api/discounts/' . $discount->getKey())
            ->assertStatus(204);

        $this->assertSoftDeleted('discounts', ['id' => $discount->id]);
    }

    public function test_delete_nonexistent_discount(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->discounts->max('id') + 1;
        $this->delete('/api/discounts/' . $nonId)
            ->assertStatus(404);
    }

    public function test_index_discounts(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/discounts');
        $response->assertStatus(200);

        $items = collect($response->json('data.data'));
        $this->assertGreaterThanOrEqual($this->discounts->count(), $items->count());

        foreach ($this->discounts as $discount) {
            $this->assertTrue(
                $items->contains(fn ($i) => $i['id'] === $discount->getKey() &&
                    $i['discount'] == $discount->discount &&
                    $i['is_percentage'] == $discount->is_percentage
                )
            );
        }
    }

    public function test_show_discount(): void
    {
        $discount = $this->discounts->first();
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/discounts/' . $discount->getKey());
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($discount->getKey(), $data['id']);
        $this->assertEquals($discount->discount, $data['discount']);
        $this->assertEquals($discount->is_percentage, $data['is_percentage']);
    }

    public function test_show_nonexistent_discount(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $nonId = $this->discounts->max('id') + 1;
        $this->get('/api/discounts/' . $nonId)
            ->assertStatus(404);
    }

    public function test_update_discount_success(): void
    {
        $discount = $this->discounts->first();
        $payload = [
            'id' => $discount->getKey(),
            'name' => 'Sommer-Special',
            'discount' => 0.3,
            'is_percentage' => false,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->put('/api/discounts', $payload);
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertDatabaseHas('discounts', [
            'id' => $data['id'],
            'name' => $payload['name'],
            'discount' => $payload['discount'],
            'is_percentage' => $payload['is_percentage'],
        ]);
    }

    public function test_update_percentage_over_one_fails(): void
    {
        $discount = $this->discounts->first();
        $payload = [
            'id' => $discount->getKey(),
            'discount' => 2,
            'is_percentage' => true,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $this->put('/api/discounts', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['discount']);
    }

    public function test_update_validation_fails(): void
    {
        $discount = $this->discounts->first();
        $payload = [
            'discount' => 0.2,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $this->put('/api/discounts', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id']);
    }
}
