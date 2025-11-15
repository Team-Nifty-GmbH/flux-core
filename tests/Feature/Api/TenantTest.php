<?php

use Carbon\Carbon;
use FluxErp\Models\Permission;
use FluxErp\Models\Tenant;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->tenants = Tenant::factory()->count(3)->create();

    $this->user->tenants()->attach($this->tenants->pluck('id')->toArray());

    $this->permissions = [
        'show' => Permission::findOrCreate('api.tenants.{id}.get'),
        'index' => Permission::findOrCreate('api.tenants.get'),
    ];
});

test('get tenant', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/tenants/' . $this->tenants[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $tenant = $json->data;
    expect($tenant)->not->toBeEmpty();
    expect($tenant->id)->toEqual($this->tenants[0]->id);
    expect($tenant->uuid)->toEqual($this->tenants[0]->uuid);
    expect($tenant->country_id)->toEqual($this->tenants[0]->country_id);
    expect($tenant->name)->toEqual($this->tenants[0]->name);
    expect($tenant->tenant_code)->toEqual($this->tenants[0]->tenant_code);
    expect($tenant->ceo)->toEqual($this->tenants[0]->ceo);
    expect($tenant->street)->toEqual($this->tenants[0]->street);
    expect($tenant->city)->toEqual($this->tenants[0]->city);
    expect($tenant->postcode)->toEqual($this->tenants[0]->postcode);
    expect($tenant->phone)->toEqual($this->tenants[0]->phone);
    expect($tenant->fax)->toEqual($this->tenants[0]->fax);
    expect($tenant->email)->toEqual($this->tenants[0]->email);
    expect($tenant->website)->toEqual($this->tenants[0]->website);
    expect($tenant->is_active)->toEqual($this->tenants[0]->is_active);
});

test('get tenant tenant not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/tenants/' . ++$this->tenants->last()->id);
    $response->assertNotFound();
});

test('get tenants', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/tenants');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonTenants = collect($json->data->data);

    // Check the amount of test tenants.
    expect(count($jsonTenants))->toBeGreaterThanOrEqual(3);

    // Check if controller returns the test tenants.
    foreach ($this->tenants as $tenant) {
        $jsonTenants->contains(function ($jsonTenant) use ($tenant) {
            return $jsonTenant->id === $tenant->id &&
                $jsonTenant->uuid === $tenant->uuid &&
                $jsonTenant->country_id === $tenant->country_id &&
                $jsonTenant->name === $tenant->name &&
                $jsonTenant->tenant_code === $tenant->tenant_code &&
                $jsonTenant->ceo === $tenant->ceo &&
                $jsonTenant->street === $tenant->street &&
                $jsonTenant->city === $tenant->city &&
                $jsonTenant->postcode === $tenant->postcode &&
                $jsonTenant->phone === $tenant->phone &&
                $jsonTenant->fax === $tenant->fax &&
                $jsonTenant->email === $tenant->email &&
                $jsonTenant->website === $tenant->website &&
                $jsonTenant->is_active === $tenant->is_active &&

                Carbon::parse($jsonTenant->created_at) === Carbon::parse($tenant->created_at) &&
                Carbon::parse($jsonTenant->updated_at) === Carbon::parse($tenant->updated_at);
        });
    }
});
