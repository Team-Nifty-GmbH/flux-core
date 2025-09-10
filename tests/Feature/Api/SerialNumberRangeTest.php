<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->products = Product::factory()
        ->count(3)
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();

    $this->serialNumberRanges = collect();
    foreach ($this->products as $product) {
        $this->serialNumberRanges->push(SerialNumberRange::factory()->create([
            'model_type' => morph_alias(Product::class),
            'model_id' => $product->id,
            'type' => 'product',
            'client_id' => $product->client_id,
        ]));
    }

    $this->serialNumbers = SerialNumber::factory()->count(3)->create();

    $this->permissions = [
        'show' => Permission::findOrCreate('api.serial-number-ranges.{id}.get'),
        'index' => Permission::findOrCreate('api.serial-number-ranges.get'),
        'create' => Permission::findOrCreate('api.serial-number-ranges.post'),
        'update' => Permission::findOrCreate('api.serial-number-ranges.put'),
        'delete' => Permission::findOrCreate('api.serial-number-ranges.{id}.delete'),
    ];
});

test('create serial number range', function (): void {
    $serialNumberRange = [
        'product_id' => $this->products[0]->id,
        'model_type' => morph_alias(Product::class),
        'type' => 'product',
        'client_id' => $this->dbClient->getKey(),
        'start_number' => rand(1, 100),
        'prefix' => Str::random(),
        'suffix' => Str::random(),
        'description' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/serial-number-ranges', $serialNumberRange);
    $response->assertCreated();

    $responseSerialNumberRange = json_decode($response->getContent())->data;

    $dbSerialNumberRange = SerialNumberRange::query()
        ->whereKey($responseSerialNumberRange->id)
        ->first();

    expect($dbSerialNumberRange->model_type)->toEqual($serialNumberRange['model_type']);
    expect($dbSerialNumberRange->model_id)->toBeNull();
    expect($dbSerialNumberRange->type)->toEqual($serialNumberRange['type']);
    expect($dbSerialNumberRange->prefix)->toEqual($serialNumberRange['prefix']);
    expect($dbSerialNumberRange->suffix)->toEqual($serialNumberRange['suffix']);
    expect($dbSerialNumberRange->description)->toEqual($serialNumberRange['description']);
});

test('create serial number range validation fails', function (): void {
    $serialNumberRange = [
        'prefix' => Str::random(),
        'suffix' => Str::random(),
        'description' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/serial-number-ranges', $serialNumberRange);
    $response->assertUnprocessable();
});

test('delete serial number range', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/serial-number-ranges/' . $this->serialNumberRanges[0]->id);
    $response->assertNoContent();

    expect(SerialNumberRange::query()->whereKey($this->serialNumberRanges[0]->id)->exists())->toBeFalse();
});

test('delete serial number range serial number range not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/serial-number-ranges/' . ++$this->serialNumberRanges[2]->id);
    $response->assertNotFound();
});

test('get serial number range', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/serial-number-ranges/' . $this->serialNumberRanges[0]->id);
    $response->assertOk();

    $serialNumberRange = json_decode($response->getContent())->data;

    expect($serialNumberRange->id)->toEqual($this->serialNumberRanges[0]->id);
    expect($serialNumberRange->model_type)->toEqual($this->serialNumberRanges[0]->model_type);
    expect($serialNumberRange->model_id)->toEqual($this->serialNumberRanges[0]->model_id);
    expect($serialNumberRange->current_number)->toEqual($this->serialNumberRanges[0]->current_number);
    expect($serialNumberRange->prefix)->toEqual($this->serialNumberRanges[0]->prefix);
    expect($serialNumberRange->suffix)->toEqual($this->serialNumberRanges[0]->suffix);
    expect($serialNumberRange->description)->toEqual($this->serialNumberRanges[0]->description);
});

test('get serial number range serial number range not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->get('/api/serial-number-ranges/' . $this->serialNumberRanges[2]->id + 10000);
    $response->assertNotFound();
});

test('get serial number ranges', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/serial-number-ranges');
    $response->assertOk();

    $serialNumberRanges = json_decode($response->getContent())->data->data;

    expect($serialNumberRanges[0]->id)->toEqual($this->serialNumberRanges[0]->id);
    expect($serialNumberRanges[0]->model_type)->toEqual($this->serialNumberRanges[0]->model_type);
    expect($serialNumberRanges[0]->model_id)->toEqual($this->serialNumberRanges[0]->model_id);
    expect($serialNumberRanges[0]->current_number)->toEqual($this->serialNumberRanges[0]->current_number);
    expect($serialNumberRanges[0]->prefix)->toEqual($this->serialNumberRanges[0]->prefix);
    expect($serialNumberRanges[0]->suffix)->toEqual($this->serialNumberRanges[0]->suffix);
    expect($serialNumberRanges[0]->description)->toEqual($this->serialNumberRanges[0]->description);
});

test('update serial number range', function (): void {
    $serialNumberRange = [
        'id' => $this->serialNumberRanges[0]->id,
        'prefix' => Str::random(),
        'suffix' => Str::random(),
        'description' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/serial-number-ranges', $serialNumberRange);
    $response->assertOk();

    $responseSerialNumberRange = json_decode($response->getContent())->data;

    $dbSerialNumberRange = SerialNumberRange::query()
        ->whereKey($responseSerialNumberRange->id)
        ->first();

    expect($dbSerialNumberRange->id)->toEqual($serialNumberRange['id']);
    expect($dbSerialNumberRange->prefix)->toEqual($serialNumberRange['prefix']);
    expect($dbSerialNumberRange->suffix)->toEqual($serialNumberRange['suffix']);
    expect($dbSerialNumberRange->description)->toEqual($serialNumberRange['description']);
    expect($dbSerialNumberRange->product_id)->toEqual($this->serialNumberRanges[0]->product_id);
    expect($dbSerialNumberRange->current_number)->toEqual($this->serialNumberRanges[0]->current_number);
});

test('update serial number range has serial number', function (): void {
    $this->serialNumbers[0]->serial_number_range_id = $this->serialNumberRanges[1]->id;
    $this->serialNumbers[0]->save();

    $serialNumberRange = [
        'id' => $this->serialNumberRanges[1]->id,
        'prefix' => Str::random(),
        'suffix' => Str::random(),
        'description' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/serial-number-ranges', $serialNumberRange);
    $response->assertStatus(423);
});

test('update serial number range validation fails', function (): void {
    $serialNumberRange = [
        'prefix' => Str::random(),
        'suffix' => Str::random(),
        'description' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/serial-number-ranges', $serialNumberRange);
    $response->assertUnprocessable();
});
