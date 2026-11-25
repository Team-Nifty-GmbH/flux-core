<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->resource = Resource::factory()->create([
        'allow_overbooking' => false,
    ]);

    $this->booking = ResourceBooking::factory()->create([
        'resource_id' => $this->resource->getKey(),
        'start' => Carbon::parse('2025-01-10 09:00:00'),
        'end' => Carbon::parse('2025-01-10 11:00:00'),
    ]);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.resource-bookings.{id}.get'),
        'index' => Permission::findOrCreate('api.resource-bookings.get'),
        'create' => Permission::findOrCreate('api.resource-bookings.post'),
        'update' => Permission::findOrCreate('api.resource-bookings.put'),
        'delete' => Permission::findOrCreate('api.resource-bookings.{id}.delete'),
    ];
});

test('create resource booking', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/resource-bookings', [
        'resource_id' => $this->resource->getKey(),
        'start' => '2025-02-10 09:00:00',
        'end' => '2025-02-10 11:00:00',
    ]);

    $response->assertCreated();

    $responseBooking = json_decode($response->getContent())->data;

    $dbBooking = ResourceBooking::query()
        ->whereKey($responseBooking->id)
        ->first();

    expect($dbBooking->resource_id)->toEqual($this->resource->getKey());
});

test('create resource booking validation fails without required fields', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/resource-bookings', [
        'resource_id' => $this->resource->getKey(),
    ]);

    $response->assertUnprocessable();
});

test('create overlapping resource booking returns 422 when overbooking not allowed', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/resource-bookings', [
        'resource_id' => $this->resource->getKey(),
        'start' => '2025-01-10 10:00:00',
        'end' => '2025-01-10 12:00:00',
    ]);

    $response->assertUnprocessable();
});

test('create overlapping resource booking succeeds when overbooking allowed', function (): void {
    $this->resource->update(['allow_overbooking' => true]);

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/resource-bookings', [
        'resource_id' => $this->resource->getKey(),
        'start' => '2025-01-10 10:00:00',
        'end' => '2025-01-10 12:00:00',
    ]);

    $response->assertCreated();
});

test('get resource booking', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/resource-bookings/' . $this->booking->getKey());
    $response->assertOk();

    $booking = json_decode($response->getContent())->data;

    expect($booking->id)->toEqual($this->booking->getKey());
    expect($booking->resource_id)->toEqual($this->resource->getKey());
});

test('get resource booking not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/resource-bookings/' . ($this->booking->getKey() + 10000));
    $response->assertNotFound();
});

test('get resource bookings', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/resource-bookings');
    $response->assertOk();

    $bookings = json_decode($response->getContent())->data->data;

    expect($bookings[0]->id)->toEqual($this->booking->getKey());
    expect($bookings[0]->resource_id)->toEqual($this->resource->getKey());
});

test('update resource booking', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/resource-bookings', [
        'id' => $this->booking->getKey(),
        'description' => 'Updated description',
    ]);

    $response->assertOk();

    $dbBooking = ResourceBooking::query()
        ->whereKey($this->booking->getKey())
        ->first();

    expect($dbBooking->description)->toEqual('Updated description');
});

test('delete resource booking', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/resource-bookings/' . $this->booking->getKey());
    $response->assertNoContent();

    expect(ResourceBooking::query()->whereKey($this->booking->getKey())->exists())->toBeFalse();
    expect(ResourceBooking::withTrashed()->whereKey($this->booking->getKey())->exists())->toBeTrue();
});
