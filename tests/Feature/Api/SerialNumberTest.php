<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class SerialNumberTest extends BaseSetup
{
    private Collection $products;

    private Collection $serialNumbers;

    private Collection $serialNumberRanges;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $warehouse = Warehouse::factory()->create();
        $this->products = Product::factory()
            ->count(3)
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
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
                'client_id' => $product->client_id,
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
    }

    public function test_get_serial_number()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/serial-numbers/' . $this->serialNumbers[0]->id);
        $response->assertStatus(200);

        $serialNumber = json_decode($response->getContent())->data;

        $this->assertEquals($this->serialNumbers[0]->id, $serialNumber->id);
        $this->assertEquals($this->serialNumbers[0]->serial_number_range_id, $serialNumber->serial_number_range_id);
        $this->assertEquals($this->serialNumbers[0]->serial_number, $serialNumber->serial_number);
        $this->assertEquals($this->serialNumbers[0]->supplier_serial_number, $serialNumber->supplier_serial_number);
    }

    public function test_get_serial_number_serial_number_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/serial-numbers/' . $this->serialNumbers[2]->id + 10000);
        $response->assertStatus(404);
    }

    public function test_get_serial_numbers()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/serial-numbers');
        $response->assertStatus(200);

        $serialNumbers = json_decode($response->getContent())->data->data;

        $this->assertEquals($this->serialNumbers[0]->id, $serialNumbers[0]->id);
        $this->assertEquals($this->serialNumbers[0]->serial_number_range_id, $serialNumbers[0]->serial_number_range_id);
        $this->assertEquals($this->serialNumbers[0]->serial_number, $serialNumbers[0]->serial_number);
        $this->assertEquals($this->serialNumbers[0]->supplier_serial_number, $serialNumbers[0]->supplier_serial_number);
    }

    public function test_create_serial_number()
    {
        $serialNumber = [
            'serial_number_range_id' => $this->serialNumberRanges[0]->id,
            'serial_number' => Str::random(),
            'supplier_serial_number' => null,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/serial-numbers', $serialNumber);
        $response->assertStatus(201);

        $responseSerialNumber = json_decode($response->getContent())->data;

        $dbSerialNumber = SerialNumber::query()
            ->whereKey($responseSerialNumber->id)
            ->first();

        $this->assertEquals($serialNumber['serial_number_range_id'], $dbSerialNumber->serial_number_range_id);
        $this->assertEquals($serialNumber['serial_number'], $dbSerialNumber->serial_number);
        $this->assertEquals($serialNumber['supplier_serial_number'], $dbSerialNumber->supplier_serial_number);
    }

    public function test_create_serial_number_from_supplier_serial_number()
    {
        $serialNumber = [
            'serial_number_range_id' => $this->serialNumberRanges[0]->id,
            'serial_number' => Str::random(),
            'supplier_serial_number' => Str::random(),
            'use_supplier_serial_number' => true,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/serial-numbers', $serialNumber);
        $response->assertStatus(201);

        $responseSerialNumber = json_decode($response->getContent())->data;

        $dbSerialNumber = SerialNumber::query()
            ->whereKey($responseSerialNumber->id)
            ->first();

        $this->assertNull($dbSerialNumber->serial_number_range_id);
        $this->assertEquals($serialNumber['supplier_serial_number'], $dbSerialNumber->serial_number);
        $this->assertEquals($serialNumber['supplier_serial_number'], $dbSerialNumber->supplier_serial_number);
    }

    public function test_create_serial_number_validation_fails()
    {
        $serialNumber = [
            'serial_number_range_id' => $this->serialNumberRanges[0]->id,
            'serial_number' => Str::random(),
            'supplier_serial_number' => null,
            'use_supplier_serial_number' => true,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/serial-numbers', $serialNumber);
        $response->assertStatus(422);
    }

    public function test_update_serial_number()
    {
        $serialNumber = [
            'id' => $this->serialNumbers[0]->id,
            'supplier_serial_number' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/serial-numbers', $serialNumber);
        $response->assertStatus(200);

        $responseSerialNumber = json_decode($response->getContent())->data;

        $dbSerialNumber = SerialNumber::query()
            ->whereKey($responseSerialNumber->id)
            ->first();

        $this->assertEquals($serialNumber['id'], $dbSerialNumber->id);
        $this->assertEquals($serialNumber['supplier_serial_number'], $dbSerialNumber->supplier_serial_number);
        $this->assertEquals($this->serialNumbers[0]->serial_number, $dbSerialNumber->serial_number);
        $this->assertEquals($this->serialNumbers[0]->serial_number_range_id, $dbSerialNumber->serial_number_range_id);
        $this->assertEquals($this->serialNumbers[0]->supp, $dbSerialNumber->supp);
    }

    public function test_update_serial_number_with_additional_columns()
    {
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
        $response->assertStatus(200);

        $responseSerialNumber = json_decode($response->getContent())->data;

        $dbSerialNumber = SerialNumber::query()
            ->whereKey($responseSerialNumber->id)
            ->first();

        $this->assertEquals($serialNumber['id'], $dbSerialNumber->id);
        $this->assertEquals($serialNumber[$additionalColumn->name], $dbSerialNumber->{$additionalColumn->name});
        $this->assertEquals($this->serialNumbers[0]->serial_number, $dbSerialNumber->serial_number);
        $this->assertEquals($this->serialNumbers[0]->serial_number_range_id, $dbSerialNumber->serial_number_range_id);
    }

    public function test_update_serial_number_validation_fails()
    {
        $serialNumber = [
            'id' => $this->serialNumbers[0]->id,
            'serial_number' => null,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/serial-numbers', $serialNumber);
        $response->assertStatus(422);
    }

    public function test_delete_serial_number()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/serial-numbers/' . $this->serialNumbers[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(SerialNumber::query()->whereKey($this->serialNumbers[0]->id)->exists());
    }

    public function test_delete_serial_number_serial_number_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/serial-numbers/' . ++$this->serialNumbers[2]->id);
        $response->assertStatus(404);
    }
}
