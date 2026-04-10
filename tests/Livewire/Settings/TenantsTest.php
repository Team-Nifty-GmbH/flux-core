<?php

use FluxErp\Livewire\Settings\Tenants;
use FluxErp\Models\Tenant;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tenants::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Tenants::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('tenant.id', null)
        ->assertSet('tenant.name', null)
        ->assertSet('tenant.ceo', null)
        ->assertSet('tenant.city', null)
        ->assertOpensModal('edit-tenant');
});

test('edit with model fills form and opens modal', function (): void {
    $tenant = Tenant::factory()->create();

    Livewire::test(Tenants::class)
        ->call('edit', $tenant->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('tenant.id', $tenant->getKey())
        ->assertSet('tenant.name', $tenant->name)
        ->assertOpensModal('edit-tenant');
});
