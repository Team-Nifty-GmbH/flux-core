<?php

use FluxErp\Jobs\SyncVariantInheritanceJob;
use FluxErp\Models\Language;
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

test('job mirrors parent attribute translations into non-overriding variants and deletes stale locales', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent EN']);
    $languageA = Language::factory()->create();
    $languageB = Language::factory()->create();

    // weight_gram-style ordering trap applies to overridden_fields too: set it explicitly
    // so InheritsFromParent::setAttribute doesn't mark 'name' as overridden on v1.
    $v1 = Product::factory()->create(['parent_id' => $parent->getKey(), 'overridden_fields' => null]);
    $v2 = Product::factory()->create(['parent_id' => $parent->getKey(), 'overridden_fields' => ['name']]);

    $insertTranslation = fn (Product $model, Language $language, string $value) => DB::table('attribute_translations')->insert([
        'language_id' => $language->getKey(),
        'model_type' => $parent->getMorphClass(),
        'model_id' => $model->getKey(),
        'attribute' => 'name',
        'value' => $value,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Parent has translations in both languages.
    $insertTranslation($parent, $languageA, 'Parent A');
    $insertTranslation($parent, $languageB, 'Parent B');

    // v1 already has stale rows for both languages (from a previous sync) that must be
    // overwritten. v2 (overriding) also has a stale row that must stay untouched.
    $insertTranslation($v1, $languageA, 'Stale A');
    $insertTranslation($v1, $languageB, 'Stale B');
    $insertTranslation($v2, $languageA, 'Should not change');

    $fetch = fn (Product $model, Language $language) => DB::table('attribute_translations')
        ->where('model_type', $parent->getMorphClass())
        ->where('model_id', $model->getKey())
        ->where('attribute', 'name')
        ->where('language_id', $language->getKey())
        ->whereNull('deleted_at')
        ->value('value');

    (new SyncVariantInheritanceJob($parent->getKey(), ['name']))->handle();

    expect($fetch($v1, $languageA))->toBe('Parent A')
        ->and($fetch($v1, $languageB))->toBe('Parent B')
        ->and($fetch($v2, $languageA))->toBe('Should not change');

    // Parent loses its languageB translation (soft-deleted, as the app does).
    DB::table('attribute_translations')
        ->where('model_type', $parent->getMorphClass())
        ->where('model_id', $parent->getKey())
        ->where('attribute', 'name')
        ->where('language_id', $languageB->getKey())
        ->update(['deleted_at' => now()]);

    (new SyncVariantInheritanceJob($parent->getKey(), ['name']))->handle();

    expect($fetch($v1, $languageA))->toBe('Parent A')
        ->and($fetch($v1, $languageB))->toBeNull()
        ->and($fetch($v2, $languageA))->toBe('Should not change');
});
