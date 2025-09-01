<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use FluxErp\Models\Permission;
use FluxErp\Models\Unit;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->unit = Unit::factory()->create();

    $this->permissions = [
        'show' => Permission::findOrCreate('api.units.{id}.get'),
        'index' => Permission::findOrCreate('api.units.get'),
        'create' => Permission::findOrCreate('api.units.post'),
        'update' => Permission::findOrCreate('api.units.put'),
        'delete' => Permission::findOrCreate('api.units.{id}.delete'),
    ];
});

test('create unit', function (): void {
    $unit = [
        'name' => Str::random(),
        'abbreviation' => Str::random(10),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/units', $unit);
    $response->assertStatus(201);

    $responseUnit = json_decode($response->getContent())->data;

    $dbUnit = Unit::query()
        ->whereKey($responseUnit->id)
        ->first();

    expect($dbUnit->name)->toEqual($unit['name']);
    expect($dbUnit->abbreviation)->toEqual($unit['abbreviation']);
});

test('create unit validation fails', function (): void {
    $unit = [
        'abbreviation' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/units', $unit);
    $response->assertStatus(422);
});

test('delete unit', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/units/' . $this->unit->id);
    $response->assertStatus(204);

    expect(Unit::query()->whereKey($this->unit->id)->exists())->toBeFalse();
});

test('delete unit unit not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/units/' . ++$this->unit->id);
    $response->assertStatus(404);
});

test('get unit', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/units/' . $this->unit->id);
    $response->assertStatus(200);

    $unit = json_decode($response->getContent())->data;

    expect($unit->id)->toEqual($this->unit->id);
    expect($unit->name)->toEqual($this->unit->name);
    expect($unit->abbreviation)->toEqual($this->unit->abbreviation);
});

test('get unit unit not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/units/' . $this->unit->id + 10000);
    $response->assertStatus(404);
});

test('get units', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/units');
    $response->assertStatus(200);

    $units = json_decode($response->getContent())->data;

    expect($units->total)->toEqual(1);
    expect($units->data[0]->id)->toEqual($this->unit->id);
    expect($units->data[0]->name)->toEqual($this->unit->name);
    expect($units->data[0]->abbreviation)->toEqual($this->unit->abbreviation);
});
