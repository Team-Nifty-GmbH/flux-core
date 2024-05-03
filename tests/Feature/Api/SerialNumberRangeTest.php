<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class SerialNumberRangeTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $products;

    private Collection $serialNumberRanges;

    private Collection $serialNumbers;

    private array $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $this->products = Product::factory()
            ->count(3)
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();

        $this->serialNumberRanges = new Collection();
        foreach ($this->products as $product) {
            $this->serialNumberRanges->push(SerialNumberRange::factory()->create([
                'model_type' => app(Product::class)->getMorphClass(),
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
    }

    public function test_get_serial_number_range()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/serial-number-ranges/' . $this->serialNumberRanges[0]->id);
        $response->assertStatus(200);

        $serialNumberRange = json_decode($response->getContent())->data;

        $this->assertEquals($this->serialNumberRanges[0]->id, $serialNumberRange->id);
        $this->assertEquals($this->serialNumberRanges[0]->model_type, $serialNumberRange->model_type);
        $this->assertEquals($this->serialNumberRanges[0]->model_id, $serialNumberRange->model_id);
        $this->assertEquals($this->serialNumberRanges[0]->current_number, $serialNumberRange->current_number);
        $this->assertEquals($this->serialNumberRanges[0]->prefix, $serialNumberRange->prefix);
        $this->assertEquals($this->serialNumberRanges[0]->suffix, $serialNumberRange->suffix);
        $this->assertEquals($this->serialNumberRanges[0]->description, $serialNumberRange->description);
    }

    public function test_get_serial_number_range_serial_number_range_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/serial-number-ranges/' . Str::uuid());
        $response->assertStatus(404);
    }

    public function test_get_serial_number_ranges()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/serial-number-ranges');
        $response->assertStatus(200);

        $serialNumberRanges = json_decode($response->getContent())->data->data;

        $this->assertEquals($this->serialNumberRanges[0]->id, $serialNumberRanges[0]->id);
        $this->assertEquals($this->serialNumberRanges[0]->model_type, $serialNumberRanges[0]->model_type);
        $this->assertEquals($this->serialNumberRanges[0]->model_id, $serialNumberRanges[0]->model_id);
        $this->assertEquals($this->serialNumberRanges[0]->current_number, $serialNumberRanges[0]->current_number);
        $this->assertEquals($this->serialNumberRanges[0]->prefix, $serialNumberRanges[0]->prefix);
        $this->assertEquals($this->serialNumberRanges[0]->suffix, $serialNumberRanges[0]->suffix);
        $this->assertEquals($this->serialNumberRanges[0]->description, $serialNumberRanges[0]->description);
    }

    public function test_create_serial_number_range()
    {
        $serialNumberRange = [
            'product_id' => $this->products[0]->id,
            'model_type' => app(Product::class)->getMorphClass(),
            'type' => 'product',
            'client_id' => $this->dbClient->id,
            'start_number' => rand(1, 100),
            'prefix' => Str::random(),
            'suffix' => Str::random(),
            'description' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/serial-number-ranges', $serialNumberRange);
        $response->assertStatus(201);

        $responseSerialNumberRange = json_decode($response->getContent())->data;

        $dbSerialNumberRange = SerialNumberRange::query()
            ->whereKey($responseSerialNumberRange->id)
            ->first();

        $this->assertEquals($serialNumberRange['model_type'], $dbSerialNumberRange->model_type);
        $this->assertNull($dbSerialNumberRange->model_id);
        $this->assertEquals($serialNumberRange['type'], $dbSerialNumberRange->type);
        $this->assertEquals($serialNumberRange['prefix'], $dbSerialNumberRange->prefix);
        $this->assertEquals($serialNumberRange['suffix'], $dbSerialNumberRange->suffix);
        $this->assertEquals($serialNumberRange['description'], $dbSerialNumberRange->description);
    }

    public function test_create_serial_number_range_validation_fails()
    {
        $serialNumberRange = [
            'prefix' => Str::random(),
            'suffix' => Str::random(),
            'description' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/serial-number-ranges', $serialNumberRange);
        $response->assertStatus(422);
    }

    public function test_update_serial_number_range()
    {
        $serialNumberRange = [
            'id' => $this->serialNumberRanges[0]->id,
            'prefix' => Str::random(),
            'suffix' => Str::random(),
            'description' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/serial-number-ranges', $serialNumberRange);
        $response->assertStatus(200);

        $responseSerialNumberRange = json_decode($response->getContent())->data;

        $dbSerialNumberRange = SerialNumberRange::query()
            ->whereKey($responseSerialNumberRange->id)
            ->first();

        $this->assertEquals($serialNumberRange['id'], $dbSerialNumberRange->id);
        $this->assertEquals($serialNumberRange['prefix'], $dbSerialNumberRange->prefix);
        $this->assertEquals($serialNumberRange['suffix'], $dbSerialNumberRange->suffix);
        $this->assertEquals($serialNumberRange['description'], $dbSerialNumberRange->description);
        $this->assertEquals($this->serialNumberRanges[0]->product_id, $dbSerialNumberRange->product_id);
        $this->assertEquals($this->serialNumberRanges[0]->current_number, $dbSerialNumberRange->current_number);
    }

    public function test_update_serial_number_range_validation_fails()
    {
        $serialNumberRange = [
            'prefix' => Str::random(),
            'suffix' => Str::random(),
            'description' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/serial-number-ranges', $serialNumberRange);
        $response->assertStatus(422);
    }

    public function test_update_serial_number_range_has_serial_number()
    {
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
    }

    public function test_delete_serial_number_range()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/serial-number-ranges/' . $this->serialNumberRanges[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(SerialNumberRange::query()->whereKey($this->serialNumberRanges[0]->id)->exists());
    }

    public function test_delete_serial_number_range_serial_number_range_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/serial-number-ranges/' . ++$this->serialNumberRanges[2]->id);
        $response->assertStatus(404);
    }
}
