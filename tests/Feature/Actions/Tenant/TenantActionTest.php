<?php

use FluxErp\Actions\Tenant\CreateTenant;
use FluxErp\Actions\Tenant\DeleteTenant;
use FluxErp\Actions\Tenant\UpdateTenant;
use FluxErp\Models\Tenant;

test('create tenant', function (): void {
    $tenant = CreateTenant::make([
        'name' => 'Acme Inc.',
        'tenant_code' => 'ACME',
    ])->validate()->execute();

    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->name->toBe('Acme Inc.');
});

test('update tenant', function (): void {
    $tenant = Tenant::factory()->create();

    $updated = UpdateTenant::make([
        'id' => $tenant->getKey(),
        'name' => 'Updated Tenant',
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated Tenant');
});

test('delete tenant', function (): void {
    $tenant = Tenant::factory()->create();

    expect(DeleteTenant::make(['id' => $tenant->getKey()])
        ->validate()->execute())->toBeTrue();
});
