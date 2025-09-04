<?php

use FluxErp\Models\Permission;
use FluxErp\Models\VatRate;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(Illuminate\Foundation\Testing\WithFaker::class);

beforeEach(function (): void {
    $this->vatRates = VatRate::factory()->count(3)->create();

    $this->permissions = [
        'show' => Permission::findOrCreate('api.vat-rates.{id}.get'),
        'index' => Permission::findOrCreate('api.vat-rates.get'),
        'create' => Permission::findOrCreate('api.vat-rates.post'),
        'update' => Permission::findOrCreate('api.vat-rates.put'),
        'delete' => Permission::findOrCreate('api.vat-rates.{id}.delete'),
    ];
});

test('create vat rate', function (): void {
    $vatRate = [
        'name' => Str::random(),
        'rate_percentage' => $this->faker->randomFloat(2, 0.01, 0.99),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/vat-rates', $vatRate);
    $response->assertCreated();

    $responseVatRate = json_decode($response->getContent())->data;

    $dbVatRate = VatRate::query()
        ->whereKey($responseVatRate->id)
        ->first();

    expect($dbVatRate->name)->toEqual($vatRate['name']);
    expect($dbVatRate->rate_percentage)->toEqual($vatRate['rate_percentage']);
});

test('create vat rate validation fails', function (): void {
    $vatRate = [
        'rate_percentage' => $this->faker->randomFloat(2, 0.01, 0.99),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/vat-rates', $vatRate);
    $response->assertUnprocessable();
});

test('delete vat rate', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/vat-rates/' . $this->vatRates[0]->id);

    $response->assertNoContent();
    expect(VatRate::query()->whereKey($this->vatRates[0]->id)->exists())->toBeFalse();
});

test('delete vat rate vat rate not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/vat-rates/' . ++$this->vatRates[2]->id);
    $response->assertNotFound();
});

test('get vat rate', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/vat-rates/' . $this->vatRates[0]->id);
    $response->assertOk();

    $vatRate = json_decode($response->getContent())->data;

    expect($vatRate->id)->toEqual($this->vatRates[0]->id);
    expect($vatRate->name)->toEqual($this->vatRates[0]->name);
    expect($vatRate->rate_percentage)->toEqual($this->vatRates[0]->rate_percentage);
});

test('get vat rate vat rate not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/vat-rates/' . $this->vatRates[2]->id + 10000);
    $response->assertNotFound();
});

test('get vat rates', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/vat-rates');
    $response->assertOk();

    $vatRates = json_decode($response->getContent())->data;

    expect($vatRates->data[1]->id)->toEqual($this->vatRates[0]->id);
    expect($vatRates->data[1]->name)->toEqual($this->vatRates[0]->name);
    expect($vatRates->data[1]->rate_percentage)->toEqual($this->vatRates[0]->rate_percentage);
});

test('update vat rate', function (): void {
    $vatRate = [
        'id' => $this->vatRates[0]->id,
        'name' => Str::random(),
        'rate_percentage' => $this->faker->randomFloat(2, 0.01, 0.99),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/vat-rates', $vatRate);
    $response->assertOk();

    $responseVatRate = json_decode($response->getContent())->data;

    $dbVatRate = VatRate::query()
        ->whereKey($responseVatRate->id)
        ->first();

    expect($dbVatRate->id)->toEqual($vatRate['id']);
    expect($dbVatRate->name)->toEqual($vatRate['name']);
    expect($dbVatRate->rate_percentage)->toEqual($vatRate['rate_percentage']);
});

test('update vat rate validation fails', function (): void {
    $vatRate = [
        'name' => Str::random(),
        'rate_percentage' => $this->faker->randomFloat(2, 0.01, 0.99),
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/vat-rates', $vatRate);
    $response->assertUnprocessable();
});
