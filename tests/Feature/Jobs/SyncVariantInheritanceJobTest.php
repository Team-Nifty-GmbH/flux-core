<?php

use FluxErp\Jobs\SyncVariantInheritanceJob;
use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;

beforeEach(fn () => app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save());

test('job copies the parent current value into non-overridden variants', function (): void {
    $parent = Product::factory()->create(['weight_gram' => 100]);
    // weight_gram must be set before parent_id: InheritsFromParent::setAttribute() marks a
    // field as overridden the moment it's assigned while parent_id is already present, so the
    // reverse order would make v1 look overridden before the job even runs.
    $v1 = Product::factory()->create(['weight_gram' => 100, 'parent_id' => $parent->getKey()]);
    $v2 = Product::factory()->create(['parent_id' => $parent->getKey(), 'weight_gram' => 100, 'overridden_fields' => ['weight_gram']]);

    Product::query()->whereKey($parent->getKey())->update(['weight_gram' => 250]); // parent already new in DB
    (new SyncVariantInheritanceJob($parent->getKey(), ['weight_gram']))->handle();

    expect((int) DB::table('products')->where('id', $v1->getKey())->value('weight_gram'))->toBe(250)
        ->and((int) DB::table('products')->where('id', $v2->getKey())->value('weight_gram'))->toBe(100);
});

test('job is a no-op when inheritance is disabled', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();
    $parent = Product::factory()->create(['weight_gram' => 100]);
    $v = Product::factory()->create(['parent_id' => $parent->getKey(), 'weight_gram' => 100]);
    Product::query()->whereKey($parent->getKey())->update(['weight_gram' => 250]);

    (new SyncVariantInheritanceJob($parent->getKey(), ['weight_gram']))->handle();

    expect((int) DB::table('products')->where('id', $v->getKey())->value('weight_gram'))->toBe(100);
});
