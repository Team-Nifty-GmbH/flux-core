<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\OrderType;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class OrderTypeTest extends BaseSetup
{
    private Collection $orderTypes;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderTypes = OrderType::factory()->count(2)->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.order-types.{id}.get'),
            'index' => Permission::findOrCreate('api.order-types.get'),
            'create' => Permission::findOrCreate('api.order-types.post'),
            'update' => Permission::findOrCreate('api.order-types.put'),
            'delete' => Permission::findOrCreate('api.order-types.{id}.delete'),
        ];
    }

    public function test_get_order_type()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/order-types/' . $this->orderTypes[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonOrderType = $json->data;

        // Check if controller returns the test order type.
        $this->assertNotEmpty($jsonOrderType);
        $this->assertEquals($this->orderTypes[0]->id, $jsonOrderType->id);
        $this->assertEquals($this->orderTypes[0]->client_id, $jsonOrderType->client_id);
        $this->assertEquals($this->orderTypes[0]->name, $jsonOrderType->name);
        $this->assertEquals($this->orderTypes[0]->description, $jsonOrderType->description);
        $this->assertEquals($this->orderTypes[0]->is_active, $jsonOrderType->is_active);
        $this->assertEquals($this->orderTypes[0]->is_hidden, $jsonOrderType->is_hidden);
        $this->assertEquals(Carbon::parse($this->orderTypes[0]->created_at),
            Carbon::parse($jsonOrderType->created_at));
        $this->assertEquals(Carbon::parse($this->orderTypes[0]->updated_at),
            Carbon::parse($jsonOrderType->updated_at));
    }

    public function test_get_order_type_order_type_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/order-types/' . ++$this->orderTypes[1]->id);
        $response->assertStatus(404);
    }

    public function test_get_order_types()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/order-types');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonOrderTypes = collect($json->data->data);

        // Check the amount of test order types.
        $this->assertGreaterThanOrEqual(2, count($jsonOrderTypes));

        // Check if controller returns the test order types.
        foreach ($this->orderTypes as $orderType) {
            $jsonOrderTypes->contains(function ($jsonOrderType) use ($orderType) {
                return $jsonOrderType->id === $orderType->id &&
                    $jsonOrderType->client_id === $orderType->client_id &&
                    $jsonOrderType->name === $orderType->name &&
                    $jsonOrderType->description === $orderType->description &&
                    $jsonOrderType->is_active === $orderType->is_active &&
                    $jsonOrderType->is_hidden === $orderType->is_hidden &&
                    Carbon::parse($jsonOrderType->created_at) === Carbon::parse($orderType->created_at) &&
                    Carbon::parse($jsonOrderType->updated_at) === Carbon::parse($orderType->updated_at);
            });
        }
    }

    public function test_create_order_type()
    {
        $orderType = [
            'client_id' => $this->orderTypes[0]->client_id,
            'name' => 'Order Type Name',
            'order_type_enum' => OrderTypeEnum::Retoure->value,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/order-types', $orderType);
        $response->assertStatus(201);

        $responseOrderType = json_decode($response->getContent())->data;
        $dbOrderType = OrderType::query()
            ->whereKey($responseOrderType->id)
            ->first();

        $this->assertNotEmpty($dbOrderType);
        $this->assertEquals($orderType['client_id'], $dbOrderType->client_id);
        $this->assertEquals($orderType['name'], $dbOrderType->name);
        $this->assertNull($dbOrderType->description);
        $this->assertEquals($orderType['order_type_enum'], $dbOrderType->order_type_enum->value);
        $this->assertTrue($dbOrderType->is_active);
        $this->assertFalse($dbOrderType->is_hidden);
        $this->assertTrue($this->user->is($dbOrderType->getCreatedBy()));
        $this->assertTrue($this->user->is($dbOrderType->getUpdatedBy()));
    }

    public function test_create_order_type_maximum()
    {
        $orderType = [
            'client_id' => $this->orderTypes[0]->client_id,
            'name' => 'Order Type Name',
            'description' => 'New description text for further information',
            'order_type_enum' => OrderTypeEnum::Retoure->value,
            'is_active' => true,
            'is_hidden' => true,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/order-types', $orderType);
        $response->assertStatus(201);

        $responseOrderType = json_decode($response->getContent())->data;
        $dbOrderType = OrderType::query()
            ->whereKey($responseOrderType->id)
            ->first();

        $this->assertNotEmpty($dbOrderType);
        $this->assertEquals($orderType['client_id'], $dbOrderType->client_id);
        $this->assertEquals($orderType['name'], $dbOrderType->name);
        $this->assertEquals($orderType['description'], $dbOrderType->description);
        $this->assertEquals($orderType['order_type_enum'], $dbOrderType->order_type_enum->value);
        $this->assertEquals($orderType['is_active'], $dbOrderType->is_active);
        $this->assertEquals($orderType['is_hidden'], $dbOrderType->is_hidden);
        $this->assertTrue($this->user->is($dbOrderType->getCreatedBy()));
        $this->assertTrue($this->user->is($dbOrderType->getUpdatedBy()));
    }

    public function test_create_order_type_validation_fails()
    {
        $orderType = [
            'client_id' => 'client_id',
            'name' => 'Order Type Name',
            'order_type_enum' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/order-types', $orderType);
        $response->assertStatus(422);
    }

    public function test_update_order_type()
    {
        $orderType = [
            'id' => $this->orderTypes[0]->id,
            'name' => 'Order Type Name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/order-types', $orderType);
        $response->assertStatus(200);

        $responseOrderType = json_decode($response->getContent())->data;
        $dbOrderType = OrderType::query()
            ->whereKey($responseOrderType->id)
            ->first();

        $this->assertNotEmpty($dbOrderType);
        $this->assertEquals($orderType['id'], $dbOrderType->id);
        $this->assertEquals($orderType['name'], $dbOrderType->name);
        $this->assertEquals($this->orderTypes[0]->description, $dbOrderType->description);
        $this->assertEquals($this->orderTypes[0]->order_type_enum, $dbOrderType->order_type_enum);
        $this->assertEquals($this->orderTypes[0]->is_active, $dbOrderType->is_active);
        $this->assertEquals($this->orderTypes[0]->is_hidden, $dbOrderType->is_hidden);
        $this->assertTrue($this->user->is($dbOrderType->getUpdatedBy()));
    }

    public function test_update_order_type_maximum()
    {
        $orderType = [
            'id' => $this->orderTypes[0]->id,
            'name' => 'Order Type Name',
            'description' => 'New description text for further information',
            'is_active' => true,
            'is_hidden' => true,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/order-types', $orderType);
        $response->assertStatus(200);

        $responseOrderType = json_decode($response->getContent())->data;
        $dbOrderType = OrderType::query()
            ->whereKey($responseOrderType->id)
            ->first();

        $this->assertNotEmpty($dbOrderType);
        $this->assertEquals($orderType['id'], $dbOrderType->id);
        $this->assertEquals($orderType['name'], $dbOrderType->name);
        $this->assertEquals($orderType['description'], $dbOrderType->description);
        $this->assertEquals($this->orderTypes[0]->order_type_enum, $dbOrderType->order_type_enum);
        $this->assertEquals($orderType['is_active'], $dbOrderType->is_active);
        $this->assertEquals($orderType['is_hidden'], $dbOrderType->is_hidden);
        $this->assertTrue($this->user->is($dbOrderType->getUpdatedBy()));
    }

    public function test_update_order_type_validation_fails()
    {
        $orderType = [
            'id' => $this->orderTypes[0]->id,
            'client_id' => 'client_id',
            'name' => 'Order Type Name',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/order-types', $orderType);
        $response->assertStatus(422);
    }

    public function test_delete_order_type()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/order-types/' . $this->orderTypes[1]->id);
        $response->assertStatus(204);

        $orderType = $this->orderTypes[1]->fresh();
        $this->assertNotNull($orderType->deleted_at);
        $this->assertTrue($this->user->is($orderType->getDeletedBy()));
    }

    public function test_delete_order_type_order_type_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/order-types/' . ++$this->orderTypes[1]->id);
        $response->assertStatus(404);
    }
}
