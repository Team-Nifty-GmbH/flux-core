<?php

use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductProperty;
use FluxErp\Settings\ProductSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();
});

it('isVariant returns true when parent_id is set', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect($variant->isVariant())->toBeTrue();
    expect($parent->isVariant())->toBeFalse();
});

it('inheritanceEnabled returns false when settings flag is false', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $product = Product::factory()->create();

    expect($product->inheritanceEnabled())->toBeFalse();
});

it('inheritanceEnabled returns true when settings flag is true', function (): void {
    $product = Product::factory()->create();

    expect($product->inheritanceEnabled())->toBeTrue();
});

it('isInheritableField returns true for fields in inheritableFields whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->isInheritableField('name'))->toBeTrue();
    expect($product->isInheritableField('parent_id'))->toBeFalse();
    expect($product->isInheritableField('product_number'))->toBeFalse();
});

it('isInheritableRelation returns true for relations in inheritableRelations whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->isInheritableRelation('prices'))->toBeTrue();
    expect($product->isInheritableRelation('orderPositions'))->toBeFalse();
});

it('overrides returns true when field is in overridden_fields', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);

    expect($variant->overrides('name'))->toBeTrue();
    expect($variant->overrides('description'))->toBeFalse();
});

it('getInheritableFields returns the configured whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->getInheritableFields())
        ->toBeArray()
        ->toContain('name', 'unit_id', 'vat_rate_id')
        ->not->toContain('parent_id', 'product_number', 'ean');
});

it('getInheritableRelations returns the configured whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->getInheritableRelations())
        ->toBeArray()
        ->toContain('prices', 'categories', 'productProperties', 'suppliers')
        ->not->toContain('orderPositions', 'crossSellings', 'tags', 'media');
});

it('returns parent value for inheritable field when not overridden', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'stale variant value',
        'overridden_fields' => null,
    ]);

    expect($variant->name)->toBe('Parent Name');
});

it('returns own value for inheritable field when overridden', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'own variant value',
        'overridden_fields' => ['name'],
    ]);

    expect($variant->name)->toBe('own variant value');
});

it('returns own value for non-inheritable field even on a variant', function (): void {
    $parent = Product::factory()->create(['product_number' => 'PARENT-001']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'product_number' => 'VARIANT-001',
    ]);

    expect($variant->product_number)->toBe('VARIANT-001');
});

it('non-variant ignores overridden_fields entirely (defensive)', function (): void {
    $product = Product::factory()->create([
        'name' => 'Standalone',
        'overridden_fields' => ['name'],
        'parent_id' => null,
    ]);

    expect($product->name)->toBe('Standalone');
});

it('falls back to own column when feature toggle is off', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'raw variant value',
        'overridden_fields' => null,
    ]);

    expect($variant->name)->toBe('raw variant value');
});

it('setAttribute on inheritable field adds it to overridden_fields', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);

    $variant->name = 'New Name';
    $variant->save();

    expect($variant->fresh()->overridden_fields)->toBe(['name']);
    expect($variant->fresh()->name)->toBe('New Name');
});

it('setAttribute is idempotent — does not duplicate field in overridden_fields', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);

    $variant->name = 'Another Name';
    $variant->save();

    expect($variant->fresh()->overridden_fields)->toBe(['name']);
});

it('setAttribute on non-inheritable field does not touch overridden_fields', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);

    $variant->product_number = 'NEW-NUM';
    $variant->save();

    expect($variant->fresh()->overridden_fields)->toBeNull();
});

it('setAttribute on non-variant does not touch overridden_fields', function (): void {
    $product = Product::factory()->create(['parent_id' => null]);

    $product->name = 'Updated';
    $product->save();

    expect($product->fresh()->overridden_fields)->toBeNull();
});

it('setting same value as parent does NOT auto-link — stays overridden', function (): void {
    $parent = Product::factory()->create(['name' => 'Same']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);

    $variant->name = 'Same';
    $variant->save();

    expect($variant->fresh()->overridden_fields)->toBe(['name']);
});

it('resetField removes a field from overridden_fields', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name', 'description'],
        'name' => 'override',
    ]);

    $variant->resetField('name');
    $variant->save();

    expect($variant->fresh()->overridden_fields)->toBe(['description']);
    expect($variant->fresh()->name)->toBe('Parent Name');
});

it('resetField on a field that is not overridden is a no-op', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['description'],
    ]);

    $variant->resetField('name');
    $variant->save();

    expect($variant->fresh()->overridden_fields)->toBe(['description']);
});

it('resetField sets overridden_fields to null when last entry is removed', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);

    $variant->resetField('name');
    $variant->save();

    expect($variant->fresh()->overridden_fields)->toBeNull();
});

it('resetField on non-inheritable field throws InvalidArgumentException', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    $variant->resetField('product_number');
})->throws(InvalidArgumentException::class, 'product_number');

it('resetFieldOnAllVariants clears the field across every variant', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent']);
    $variantA = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name', 'description'],
    ]);
    $variantB = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);
    $variantC = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);

    $touched = $parent->resetFieldOnAllVariants('name');

    expect($touched)->toBe(2);
    expect($variantA->fresh()->overridden_fields)->toBe(['description']);
    expect($variantB->fresh()->overridden_fields)->toBeNull();
    expect($variantC->fresh()->overridden_fields)->toBeNull();
});

it('resetFieldOnAllVariants throws on non-inheritable field', function (): void {
    $parent = Product::factory()->create();

    $parent->resetFieldOnAllVariants('product_number');
})->throws(InvalidArgumentException::class, 'product_number');

it('resetFieldOnAllVariants returns 0 when no variants exist', function (): void {
    $parent = Product::factory()->create();

    expect($parent->resetFieldOnAllVariants('name'))->toBe(0);
});

it('variant inherits parent prices for price lists where it has no own price', function (): void {
    $listA = PriceList::factory()->create();
    $listB = PriceList::factory()->create();

    $parent = Product::factory()->create();
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => 10,
    ]);
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $listB->getKey(),
        'price' => 20,
    ]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    Price::factory()->create([
        'product_id' => $variant->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => 15,
    ]);

    $effective = $variant->prices->keyBy('price_list_id');

    expect($effective->get($listA->getKey())->price)->toEqual(15);
    expect($effective->get($listB->getKey())->price)->toEqual(20);
});

it('variant inherits all parent prices when no own prices set', function (): void {
    $listA = PriceList::factory()->create();
    $parent = Product::factory()->create();
    Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => 10,
    ]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect($variant->prices)->toHaveCount(1);
    expect($variant->prices->first()->price)->toEqual(10);
});

it('non-variant returns own prices unchanged', function (): void {
    $listA = PriceList::factory()->create();
    $product = Product::factory()->create();
    Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => 5,
    ]);

    expect($product->prices)->toHaveCount(1);
});

it('variant inherits parent categories not overridden', function (): void {
    $a = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $b = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $c = Category::factory()->create(['model_type' => morph_alias(Product::class)]);

    $parent = Product::factory()->create();
    $parent->ownCategories()->attach([$a->getKey(), $b->getKey()]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownCategories()->attach([$c->getKey()]);

    $ids = $variant->categories->pluck('id')->sort()->values()->all();
    $expected = collect([$a->getKey(), $b->getKey(), $c->getKey()])->sort()->values()->all();

    expect($ids)->toBe($expected);
});

it('variant inherits all parent categories when no own', function (): void {
    $a = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create();
    $parent->ownCategories()->attach([$a->getKey()]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect($variant->categories->pluck('id')->all())->toBe([$a->getKey()]);
});

it('non-variant returns own categories unchanged', function (): void {
    $a = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $product = Product::factory()->create();
    $product->ownCategories()->attach([$a->getKey()]);

    expect($product->categories->pluck('id')->all())->toBe([$a->getKey()]);
});

it('variant inherits parent productProperties not overridden', function (): void {
    $propA = ProductProperty::factory()->create();
    $propB = ProductProperty::factory()->create();
    $propC = ProductProperty::factory()->create();

    $parent = Product::factory()->create();
    $parent->ownProductProperties()->attach([
        $propA->getKey() => ['value' => 'red'],
        $propB->getKey() => ['value' => '10kg'],
    ]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownProductProperties()->attach([
        $propC->getKey() => ['value' => 'fast'],
    ]);

    $ids = $variant->productProperties->pluck('id')->sort()->values()->all();
    $expected = collect([$propA->getKey(), $propB->getKey(), $propC->getKey()])
        ->sort()
        ->values()
        ->all();

    expect($ids)->toBe($expected);
});

it('variant inherits parent productProperties pivot value when not overridden', function (): void {
    $propA = ProductProperty::factory()->create();
    $propB = ProductProperty::factory()->create();

    $parent = Product::factory()->create();
    $parent->ownProductProperties()->attach([
        $propA->getKey() => ['value' => 'red'],
        $propB->getKey() => ['value' => '10kg'],
    ]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownProductProperties()->attach([
        $propA->getKey() => ['value' => 'blue'],
    ]);

    $effective = $variant->productProperties->keyBy('id');

    expect($effective->get($propA->getKey())->pivot->value)->toBe('blue');
    expect($effective->get($propB->getKey())->pivot->value)->toBe('10kg');
});

it('variant inherits parent suppliers not overridden', function (): void {
    $a = Contact::factory()->create();
    $b = Contact::factory()->create();
    $c = Contact::factory()->create();

    $parent = Product::factory()->create();
    $parent->ownSuppliers()->attach([$a->getKey(), $b->getKey()]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownSuppliers()->attach([$c->getKey()]);

    $ids = $variant->suppliers->pluck('id')->sort()->values()->all();
    $expected = collect([$a->getKey(), $b->getKey(), $c->getKey()])
        ->sort()
        ->values()
        ->all();

    expect($ids)->toBe($expected);
});

it('variant inherits all parent suppliers when no own', function (): void {
    $a = Contact::factory()->create();
    $parent = Product::factory()->create();
    $parent->ownSuppliers()->attach([$a->getKey()]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect($variant->suppliers->pluck('id')->all())->toBe([$a->getKey()]);
});

it('media is intentionally NOT inherited (Spatie escape hatch)', function (): void {
    $parent = Product::factory()->create();
    $parent->addMedia(UploadedFile::fake()->image('p.jpg'))
        ->toMediaCollection('images');

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect($variant->media)->toHaveCount(0);
    expect($variant->getInheritableRelations())->not->toContain('media');
});

it('resetRelation removes a single override (specific key) for HasMany prices', function (): void {
    $listA = PriceList::factory()->create();
    $listB = PriceList::factory()->create();

    $parent = Product::factory()->create();
    Price::factory()->create(['product_id' => $parent->getKey(), 'price_list_id' => $listA->getKey(), 'price' => 10]);
    Price::factory()->create(['product_id' => $parent->getKey(), 'price_list_id' => $listB->getKey(), 'price' => 20]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    Price::factory()->create(['product_id' => $variant->getKey(), 'price_list_id' => $listA->getKey(), 'price' => 15]);
    Price::factory()->create(['product_id' => $variant->getKey(), 'price_list_id' => $listB->getKey(), 'price' => 25]);

    $variant->resetRelation('prices', $listA->getKey());

    expect($variant->ownPrices()->count())->toBe(1);
    expect($variant->prices->where('price_list_id', $listA->getKey())->first()->price)->toEqual(10);
});

it('resetRelation without key removes all variant pivot rows for BelongsToMany categories', function (): void {
    $catA = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $catB = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create();
    $parent->ownCategories()->attach([$catA->getKey()]);

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownCategories()->attach([$catA->getKey(), $catB->getKey()]);

    $variant->resetRelation('categories');

    expect($variant->ownCategories()->count())->toBe(0);
});

it('resetRelationOnAllVariants removes pivot rows on every variant', function (): void {
    $catA = Category::factory()->create(['model_type' => morph_alias(Product::class)]);
    $parent = Product::factory()->create();
    $variantA = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variantA->ownCategories()->attach([$catA->getKey()]);
    $variantB = Product::factory()->create(['parent_id' => $parent->getKey()]);
    $variantB->ownCategories()->attach([$catA->getKey()]);

    $touched = $parent->resetRelationOnAllVariants('categories', $catA->getKey());

    expect($touched)->toBe(2);
    expect($variantA->ownCategories()->count())->toBe(0);
    expect($variantB->ownCategories()->count())->toBe(0);
});

it('resetRelation throws on non-inheritable relation', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    $variant->resetRelation('orderPositions');
})->throws(InvalidArgumentException::class, 'orderPositions');

it('resetFieldOnAllVariants issues a bounded number of queries (set-based)', function (): void {
    $parent = Product::factory()->create();
    Product::factory()->count(10)->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);

    DB::enableQueryLog();
    $parent->resetFieldOnAllVariants('name');
    $log = collect(DB::getQueryLog())
        ->reject(fn (array $entry): bool => str_contains($entry['query'], 'from `languages`'))
        ->values();
    DB::disableQueryLog();

    // Pre-refactor: 1 children select + N per-variant saves => grows with N.
    // Post-refactor: 1 children select + 1 grouped UPDATE (transaction-wrapped, BEGIN/COMMIT
    // not emitted by the query log) => 2 non-language queries regardless of variant count.
    expect($log->count())->toBeLessThan(5);
});

it('resetRelationOnAllVariants issues a bounded number of queries for BelongsToMany', function (): void {
    $cat = Category::factory()->create([
        'model_type' => morph_alias(Product::class),
    ]);
    $parent = Product::factory()->create();
    $variants = Product::factory()->count(5)->create(['parent_id' => $parent->getKey()]);
    foreach ($variants as $variant) {
        $variant->ownCategories()->attach([$cat->getKey()]);
    }

    DB::enableQueryLog();
    $parent->resetRelationOnAllVariants('categories', $cat->getKey());
    $log = collect(DB::getQueryLog())
        ->reject(fn (array $entry): bool => str_contains($entry['query'], 'from `languages`'))
        ->values();
    DB::disableQueryLog();

    // Post-refactor: 1 children pluck + 1 sample variant select + 1 distinct count + 1 delete
    // = 4 non-language queries regardless of variant count.
    expect($log->count())->toBeLessThan(6);
});
