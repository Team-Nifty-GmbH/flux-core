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
