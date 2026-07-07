<?php

use FluxErp\Models\Product;

beforeEach(function (): void {
    app(FluxErp\Settings\ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();
});

test('runs the migration for the default tenant when feature toggle is on', function (): void {
    $parent = Product::factory()->create(['name' => 'Same']);
    Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'Same',
    ]);

    $this->artisan('flux:product-variants:migrate-inheritance')
        ->expectsOutputToContain('processed')
        ->assertSuccessful();
});

test('refuses to run when feature toggle is off', function (): void {
    app(FluxErp\Settings\ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $this->artisan('flux:product-variants:migrate-inheritance')
        ->expectsOutputToContain('disabled')
        ->assertFailed();
});

test('scopes processing to a single parent when --parent-id is given', function (): void {
    // Simulate legacy/stale data (inheritance off while creating) so materialization
    // has stale columns to repair, same setup as the job's own scoping test.
    app(FluxErp\Settings\ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $parentA = Product::factory()->create(['weight_gram' => 100]);
    $variantA = Product::factory()->create(['parent_id' => $parentA->getKey(), 'weight_gram' => 1]);

    $parentB = Product::factory()->create(['weight_gram' => 200]);
    $variantB = Product::factory()->create(['parent_id' => $parentB->getKey(), 'weight_gram' => 1]);

    app(FluxErp\Settings\ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $this->artisan('flux:product-variants:migrate-inheritance', ['--parent-id' => $parentA->getKey()])
        ->assertSuccessful();

    expect((int) DB::table('products')->where('id', $variantA->getKey())->value('weight_gram'))->toBe(100)
        ->and((int) DB::table('products')->where('id', $variantB->getKey())->value('weight_gram'))->toBe(1);
});
