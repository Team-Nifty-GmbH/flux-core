<?php

use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;

beforeEach(function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();
});

test('variant column holds the real inherited value in the database', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent', 'weight_gram' => 100]);
    $variant = Product::factory()->create(['parent_id' => $parent->getKey(), 'weight_gram' => 100]);

    // SQL-level truth, not accessor: the raw column must equal the parent value.
    expect((int) DB::table('products')->where('id', $variant->getKey())->value('weight_gram'))
        ->toBe(100);
});

test('editing a parent field propagates to non-overridden variants at the SQL level', function (): void {
    $parent = Product::factory()->create(['weight_gram' => 100]);
    $variant = Product::factory()->create(['parent_id' => $parent->getKey(), 'weight_gram' => 100]);

    $parent->update(['weight_gram' => 250]);
    FluxErp\Jobs\SyncVariantInheritanceJob::dispatchSync($parent->getKey(), ['weight_gram']);

    expect((int) DB::table('products')->where('id', $variant->getKey())->value('weight_gram'))
        ->toBe(250);
});

test('an overridden field is not overwritten by a parent change', function (): void {
    $parent = Product::factory()->create(['weight_gram' => 100]);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'weight_gram' => 999,
        'overridden_fields' => ['weight_gram'],
    ]);

    $parent->update(['weight_gram' => 250]);
    FluxErp\Jobs\SyncVariantInheritanceJob::dispatchSync($parent->getKey(), ['weight_gram']);

    expect((int) DB::table('products')->where('id', $variant->getKey())->value('weight_gram'))
        ->toBe(999);
});
