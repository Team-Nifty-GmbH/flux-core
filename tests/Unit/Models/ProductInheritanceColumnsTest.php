<?php

use FluxErp\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('has overridden_fields json column on products', function (): void {
    expect(Schema::hasColumn('products', 'overridden_fields'))->toBeTrue();

    $product = Product::factory()->create(['overridden_fields' => ['name']]);
    expect($product->fresh()->overridden_fields)->toBe(['name']);
});

it('has was_parent boolean column on products defaulting to false', function (): void {
    expect(Schema::hasColumn('products', 'was_parent'))->toBeTrue();

    $product = Product::factory()->create();
    expect($product->fresh()->was_parent)->toBeFalse();
});

it('backfills was_parent for products with existing children', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    DB::table('products')->where('id', $parent->getKey())->update(['was_parent' => false]);
    expect($parent->fresh()->was_parent)->toBeFalse();

    $migration = require __DIR__ . '/../../../database/migrations/2026_05_07_120050_backfill_was_parent_on_existing_products.php';
    $migration->up();

    expect($parent->fresh()->was_parent)->toBeTrue();
    expect($variant->fresh()->was_parent)->toBeFalse();
});
