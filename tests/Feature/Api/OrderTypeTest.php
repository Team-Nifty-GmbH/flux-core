<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\OrderType;
use FluxErp\Models\Permission;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
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
});

test('create order type', function (): void {
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

    expect($dbOrderType)->not->toBeEmpty();
    expect($dbOrderType->client_id)->toEqual($orderType['client_id']);
    expect($dbOrderType->name)->toEqual($orderType['name']);
    expect($dbOrderType->description)->toBeNull();
    expect($dbOrderType->order_type_enum->value)->toEqual($orderType['order_type_enum']);
    expect($dbOrderType->is_active)->toBeTrue();
    expect($dbOrderType->is_hidden)->toBeFalse();
    expect($this->user->is($dbOrderType->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbOrderType->getUpdatedBy()))->toBeTrue();
});

test('create order type maximum', function (): void {
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

    expect($dbOrderType)->not->toBeEmpty();
    expect($dbOrderType->client_id)->toEqual($orderType['client_id']);
    expect($dbOrderType->name)->toEqual($orderType['name']);
    expect($dbOrderType->description)->toEqual($orderType['description']);
    expect($dbOrderType->order_type_enum->value)->toEqual($orderType['order_type_enum']);
    expect($dbOrderType->is_active)->toEqual($orderType['is_active']);
    expect($dbOrderType->is_hidden)->toEqual($orderType['is_hidden']);
    expect($this->user->is($dbOrderType->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbOrderType->getUpdatedBy()))->toBeTrue();
});

test('create order type validation fails', function (): void {
    $orderType = [
        'client_id' => 'client_id',
        'name' => 'Order Type Name',
        'order_type_enum' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/order-types', $orderType);
    $response->assertStatus(422);
});

test('delete order type', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/order-types/' . $this->orderTypes[1]->id);
    $response->assertStatus(204);

    $orderType = $this->orderTypes[1]->fresh();
    expect($orderType->deleted_at)->not->toBeNull();
    expect($this->user->is($orderType->getDeletedBy()))->toBeTrue();
});

test('delete order type order type not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/order-types/' . ++$this->orderTypes[1]->id);
    $response->assertStatus(404);
});

test('get order type', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/order-types/' . $this->orderTypes[0]->id);
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonOrderType = $json->data;

    // Check if controller returns the test order type.
    expect($jsonOrderType)->not->toBeEmpty();
    expect($jsonOrderType->id)->toEqual($this->orderTypes[0]->id);
    expect($jsonOrderType->client_id)->toEqual($this->orderTypes[0]->client_id);
    expect($jsonOrderType->name)->toEqual($this->orderTypes[0]->name);
    expect($jsonOrderType->description)->toEqual($this->orderTypes[0]->description);
    expect($jsonOrderType->is_active)->toEqual($this->orderTypes[0]->is_active);
    expect($jsonOrderType->is_hidden)->toEqual($this->orderTypes[0]->is_hidden);
    expect(Carbon::parse($jsonOrderType->created_at))->toEqual(Carbon::parse($this->orderTypes[0]->created_at));
    expect(Carbon::parse($jsonOrderType->updated_at))->toEqual(Carbon::parse($this->orderTypes[0]->updated_at));
});

test('get order type order type not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/order-types/' . ++$this->orderTypes[1]->id);
    $response->assertStatus(404);
});

test('get order types', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/order-types');
    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $jsonOrderTypes = collect($json->data->data);

    // Check the amount of test order types.
    expect(count($jsonOrderTypes))->toBeGreaterThanOrEqual(2);

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
});

test('update order type', function (): void {
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

    expect($dbOrderType)->not->toBeEmpty();
    expect($dbOrderType->id)->toEqual($orderType['id']);
    expect($dbOrderType->name)->toEqual($orderType['name']);
    expect($dbOrderType->description)->toEqual($this->orderTypes[0]->description);
    expect($dbOrderType->order_type_enum)->toEqual($this->orderTypes[0]->order_type_enum);
    expect($dbOrderType->is_active)->toEqual($this->orderTypes[0]->is_active);
    expect($dbOrderType->is_hidden)->toEqual($this->orderTypes[0]->is_hidden);
    expect($this->user->is($dbOrderType->getUpdatedBy()))->toBeTrue();
});

test('update order type maximum', function (): void {
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

    expect($dbOrderType)->not->toBeEmpty();
    expect($dbOrderType->id)->toEqual($orderType['id']);
    expect($dbOrderType->name)->toEqual($orderType['name']);
    expect($dbOrderType->description)->toEqual($orderType['description']);
    expect($dbOrderType->order_type_enum)->toEqual($this->orderTypes[0]->order_type_enum);
    expect($dbOrderType->is_active)->toEqual($orderType['is_active']);
    expect($dbOrderType->is_hidden)->toEqual($orderType['is_hidden']);
    expect($this->user->is($dbOrderType->getUpdatedBy()))->toBeTrue();
});

test('update order type validation fails', function (): void {
    $orderType = [
        'id' => $this->orderTypes[0]->id,
        'client_id' => 'client_id',
        'name' => 'Order Type Name',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/order-types', $orderType);
    $response->assertStatus(422);
});
