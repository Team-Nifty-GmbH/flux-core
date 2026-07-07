<?php

use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;

test('backfill migration materializes stale variant columns and seeds inherited relation rows', function (): void {
    // Legacy data: written before materialization existed, so the setting was off and
    // the child column/relations were never synced to the parent.
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $parent = Product::factory()->create(['weight_gram' => 100]);
    $variant = Product::factory()->create(['parent_id' => $parent->getKey(), 'weight_gram' => 1]);

    $list = PriceList::factory()->create();
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $list->getKey(),
        'price' => '100.0000',
    ]);

    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $migration = require __DIR__ . '/../../../database/migrations/2026_07_07_120000_backfill_materialized_variant_values.php';
    $migration->up();

    expect((int) DB::table('products')->where('id', $variant->getKey())->value('weight_gram'))
        ->toBe(100);

    $priceRow = DB::table('prices')
        ->where('product_id', $variant->getKey())
        ->where('price_list_id', $list->getKey())
        ->whereNull('deleted_at')
        ->first();

    expect($priceRow)->not->toBeNull()
        ->and((bool) $priceRow->is_inherited)->toBeTrue()
        ->and((float) $priceRow->price)->toBe(100.0);
});
