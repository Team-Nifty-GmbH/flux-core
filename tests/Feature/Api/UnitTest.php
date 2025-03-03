<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Permission;
use FluxErp\Models\Unit;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class UnitTest extends BaseSetup
{
    private Model $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::factory()->create();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.units.{id}.get'),
            'index' => Permission::findOrCreate('api.units.get'),
            'create' => Permission::findOrCreate('api.units.post'),
            'update' => Permission::findOrCreate('api.units.put'),
            'delete' => Permission::findOrCreate('api.units.{id}.delete'),
        ];
    }

    public function test_get_unit()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/units/' . $this->unit->id);
        $response->assertStatus(200);

        $unit = json_decode($response->getContent())->data;

        $this->assertEquals($this->unit->id, $unit->id);
        $this->assertEquals($this->unit->name, $unit->name);
        $this->assertEquals($this->unit->abbreviation, $unit->abbreviation);
    }

    public function test_get_unit_unit_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/units/' . $this->unit->id + 10000);
        $response->assertStatus(404);
    }

    public function test_get_units()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/units');
        $response->assertStatus(200);

        $units = json_decode($response->getContent())->data;

        $this->assertEquals(1, $units->total);
        $this->assertEquals($this->unit->id, $units->data[0]->id);
        $this->assertEquals($this->unit->name, $units->data[0]->name);
        $this->assertEquals($this->unit->abbreviation, $units->data[0]->abbreviation);
    }

    public function test_create_unit()
    {
        $unit = [
            'name' => Str::random(),
            'abbreviation' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/units', $unit);
        $response->assertStatus(201);

        $responseUnit = json_decode($response->getContent())->data;

        $dbUnit = Unit::query()
            ->whereKey($responseUnit->id)
            ->first();

        $this->assertEquals($unit['name'], $dbUnit->name);
        $this->assertEquals($unit['abbreviation'], $dbUnit->abbreviation);
    }

    public function test_create_unit_validation_fails()
    {
        $unit = [
            'abbreviation' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/units', $unit);
        $response->assertStatus(422);
    }

    public function test_delete_unit()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/units/' . $this->unit->id);
        $response->assertStatus(204);

        $this->assertFalse(Unit::query()->whereKey($this->unit->id)->exists());
    }

    public function test_delete_unit_unit_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/units/' . ++$this->unit->id);
        $response->assertStatus(404);
    }
}
