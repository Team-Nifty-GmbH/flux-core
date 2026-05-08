<?php

use FluxErp\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

it('has product_variant_inheritance_enabled boolean column on tenants with default false in schema', function (): void {
    expect(Schema::hasColumn('tenants', 'product_variant_inheritance_enabled'))->toBeTrue();

    // Insert via raw DB to bypass the model creating hook — verifies the *column* default.
    $id = DB::table('tenants')->insertGetId([
        'name' => 'Schema Default Test',
        'tenant_code' => 'schema-default-' . Str::random(8),
        'uuid' => (string) Str::uuid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $value = DB::table('tenants')->where('id', $id)->value('product_variant_inheritance_enabled');
    expect((bool) $value)->toBeFalse();
});

it('defaults product_variant_inheritance_enabled to true for tenants created via the model', function (): void {
    $tenant = Tenant::factory()->create();
    expect($tenant->fresh()->product_variant_inheritance_enabled)->toBeTrue();
});

it('persists product_variant_inheritance_enabled when set to true', function (): void {
    $tenant = Tenant::factory()->create(['product_variant_inheritance_enabled' => true]);
    expect($tenant->fresh()->product_variant_inheritance_enabled)->toBeTrue();
});

it('respects explicit product_variant_inheritance_enabled = false on creation', function (): void {
    $tenant = Tenant::factory()->create(['product_variant_inheritance_enabled' => false]);
    expect($tenant->fresh()->product_variant_inheritance_enabled)->toBeFalse();
});

it('evicts default-tenant cache when product_variant_inheritance_enabled is toggled', function (): void {
    // Start from a known-false baseline so we can observe the toggle taking effect.
    $tenant = Tenant::default();
    $tenant->update(['product_variant_inheritance_enabled' => false]);
    Tenant::clearDefaultCache();

    expect(Tenant::default()->product_variant_inheritance_enabled)->toBeFalse();

    // Memoize the default tenant via a read.
    Tenant::default();

    // Toggle the flag.
    $tenant->update(['product_variant_inheritance_enabled' => true]);

    // Tenant::default() should now reflect the change because the saving hook evicted the memo.
    expect(Tenant::default()->product_variant_inheritance_enabled)->toBeTrue();
});

test('clearDefaultCache evicts the default-tenant memo', function (): void {
    $tenant = Tenant::default();          // memoize attributes

    // Mutate the underlying row directly via DB (bypasses model events that would clear the cache).
    DB::table('tenants')->where('id', $tenant->getKey())->update(['name' => 'NewName']);

    // Without clearing, default() returns memoized (stale) attributes.
    expect(Tenant::default()->name)->toBe($tenant->name);

    Tenant::clearDefaultCache();

    // After clearing, default() recomputes from DB and reflects the change.
    expect(Tenant::default()->name)->toBe('NewName');
});
