<?php

use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $warehouse = Warehouse::factory()->create();
    $this->products = Product::factory()
        ->count(3)
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create([
            'has_serial_numbers' => true,
        ]);

    $this->serialNumbers = SerialNumber::factory()->count(3)->create();

    $this->serialNumberRanges = collect();
    foreach ($this->products as $key => $product) {
        $this->serialNumberRanges->push(SerialNumberRange::factory()->create([
            'model_type' => Product::class,
            'model_id' => $product->id,
            'type' => 'product',
            'tenant_id' => $product->tenant_id,
        ]));

        StockPosting::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'serial_number_id' => $this->serialNumbers[$key]->id,
            'posting' => 1,
        ]);
    }

    $this->permissions = [
        'show' => Permission::findOrCreate('api.serial-numbers.{id}.get'),
        'index' => Permission::findOrCreate('api.serial-numbers.get'),
        'create' => Permission::findOrCreate('api.serial-numbers.post'),
        'update' => Permission::findOrCreate('api.serial-numbers.put'),
        'delete' => Permission::findOrCreate('api.serial-numbers.{id}.delete'),
    ];
});

test('create serial number', function (): void {
    $serialNumber = [
        'serial_number_range_id' => $this->serialNumberRanges[0]->id,
        'serial_number' => Str::random(),
        'supplier_serial_number' => null,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/serial-numbers', $serialNumber);
    $response->assertCreated();

    $responseSerialNumber = json_decode($response->getContent())->data;

    $dbSerialNumber = SerialNumber::query()
        ->whereKey($responseSerialNumber->id)
        ->first();

    expect($dbSerialNumber->serial_number_range_id)->toEqual($serialNumber['serial_number_range_id']);
    expect($dbSerialNumber->serial_number)->toEqual($serialNumber['serial_number']);
    expect($dbSerialNumber->supplier_serial_number)->toEqual($serialNumber['supplier_serial_number']);
});

test('create serial number from supplier serial number', function (): void {
    $serialNumber = [
        'serial_number_range_id' => $this->serialNumberRanges[0]->id,
        'serial_number' => Str::random(),
        'supplier_serial_number' => Str::random(),
        'use_supplier_serial_number' => true,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/serial-numbers', $serialNumber);
    $response->assertCreated();

    $responseSerialNumber = json_decode($response->getContent())->data;

    $dbSerialNumber = SerialNumber::query()
        ->whereKey($responseSerialNumber->id)
        ->first();

    expect($dbSerialNumber->serial_number_range_id)->toBeNull();
    expect($dbSerialNumber->serial_number)->toEqual($serialNumber['supplier_serial_number']);
    expect($dbSerialNumber->supplier_serial_number)->toEqual($serialNumber['supplier_serial_number']);
});

test('create serial number validation fails', function (): void {
    $serialNumber = [
        'serial_number_range_id' => $this->serialNumberRanges[0]->id,
        'serial_number' => Str::random(),
        'supplier_serial_number' => null,
        'use_supplier_serial_number' => true,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/serial-numbers', $serialNumber);
    $response->assertUnprocessable();
});

test('delete serial number', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/serial-numbers/' . $this->serialNumbers[0]->id);
    $response->assertNoContent();

    expect(SerialNumber::query()->whereKey($this->serialNumbers[0]->id)->exists())->toBeFalse();
});

test('delete serial number serial number not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/serial-numbers/' . ++$this->serialNumbers[2]->id);
    $response->assertNotFound();
});

test('get serial number', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/serial-numbers/' . $this->serialNumbers[0]->id);
    $response->assertOk();

    $serialNumber = json_decode($response->getContent())->data;

    expect($serialNumber->id)->toEqual($this->serialNumbers[0]->id);
    expect($serialNumber->serial_number_range_id)->toEqual($this->serialNumbers[0]->serial_number_range_id);
    expect($serialNumber->serial_number)->toEqual($this->serialNumbers[0]->serial_number);
    expect($serialNumber->supplier_serial_number)->toEqual($this->serialNumbers[0]->supplier_serial_number);
});

test('get serial number serial number not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/serial-numbers/' . $this->serialNumbers[2]->id + 10000);
    $response->assertNotFound();
});

test('get serial numbers', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/serial-numbers');
    $response->assertOk();

    $serialNumbers = json_decode($response->getContent())->data->data;

    expect($serialNumbers[0]->id)->toEqual($this->serialNumbers[0]->id);
    expect($serialNumbers[0]->serial_number_range_id)->toEqual($this->serialNumbers[0]->serial_number_range_id);
    expect($serialNumbers[0]->serial_number)->toEqual($this->serialNumbers[0]->serial_number);
    expect($serialNumbers[0]->supplier_serial_number)->toEqual($this->serialNumbers[0]->supplier_serial_number);
});

test('update serial number', function (): void {
    $serialNumber = [
        'id' => $this->serialNumbers[0]->id,
        'supplier_serial_number' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/serial-numbers', $serialNumber);
    $response->assertOk();

    $responseSerialNumber = json_decode($response->getContent())->data;

    $dbSerialNumber = SerialNumber::query()
        ->whereKey($responseSerialNumber->id)
        ->first();

    expect($dbSerialNumber->id)->toEqual($serialNumber['id']);
    expect($dbSerialNumber->supplier_serial_number)->toEqual($serialNumber['supplier_serial_number']);
    expect($dbSerialNumber->serial_number)->toEqual($this->serialNumbers[0]->serial_number);
    expect($dbSerialNumber->serial_number_range_id)->toEqual($this->serialNumbers[0]->serial_number_range_id);
    expect($dbSerialNumber->supp)->toEqual($this->serialNumbers[0]->supp);
});

test('update serial number validation fails', function (): void {
    $serialNumber = [
        'id' => $this->serialNumbers[0]->id,
        'serial_number' => null,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/serial-numbers', $serialNumber);
    $response->assertUnprocessable();
});

test('update serial number with additional columns', function (): void {
    $additionalColumn = AdditionalColumn::factory()->create([
        'model_type' => morph_alias(SerialNumber::class),
    ]);

    $serialNumber = [
        'id' => $this->serialNumbers[0]->id,
        $additionalColumn->name => 'New Value',
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/serial-numbers', $serialNumber);
    $response->assertOk();

    $responseSerialNumber = json_decode($response->getContent())->data;

    $dbSerialNumber = SerialNumber::query()
        ->whereKey($responseSerialNumber->id)
        ->first();

    expect($dbSerialNumber->id)->toEqual($serialNumber['id']);
    expect($dbSerialNumber->{$additionalColumn->name})->toEqual($serialNumber[$additionalColumn->name]);
    expect($dbSerialNumber->serial_number)->toEqual($this->serialNumbers[0]->serial_number);
    expect($dbSerialNumber->serial_number_range_id)->toEqual($this->serialNumbers[0]->serial_number_range_id);
});
