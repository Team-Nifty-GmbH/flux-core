<?php

use FluxErp\Models\Lot;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->product = Product::factory()->create();
    $this->lots = Lot::factory()->count(3)->create(['product_id' => $this->product->getKey()]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.lots.{id}.get'),
        'index' => Permission::findOrCreate('api.lots.get'),
        'create' => Permission::findOrCreate('api.lots.post'),
        'update' => Permission::findOrCreate('api.lots.put'),
        'delete' => Permission::findOrCreate('api.lots.{id}.delete'),
    ];
});

test('get lot', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/lots/' . $this->lots[0]->id);
    $response->assertOk();

    expect(json_decode($response->getContent())->data->lot_number)->toEqual($this->lots[0]->lot_number);
});

test('get lots', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/lots');
    $response->assertOk();
});

test('create lot', function (): void {
    $lot = [
        'product_id' => $this->product->getKey(),
        'lot_number' => Str::random(),
        'expires_at' => '2027-06-30',
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/lots', $lot);
    $response->assertCreated();

    $responseLot = json_decode($response->getContent())->data;
    expect(Lot::query()->whereKey($responseLot->id)->first()->lot_number)->toEqual($lot['lot_number']);
});

test('create lot validation fails', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/lots', ['lot_number' => Str::random()]);
    $response->assertUnprocessable();
});

test('update lot', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/lots', [
        'id' => $this->lots[0]->id,
        'supplier_lot_number' => 'SUP-1',
    ]);
    $response->assertOk();

    expect($this->lots[0]->fresh()->supplier_lot_number)->toEqual('SUP-1');
});

test('delete lot', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/lots/' . $this->lots[0]->id);
    $response->assertNoContent();
});
