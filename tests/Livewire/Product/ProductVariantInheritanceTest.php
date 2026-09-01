<?php

use FluxErp\Livewire\Product\Product;
use FluxErp\Models\Category;
use FluxErp\Models\Product as ProductModel;
use FluxErp\Models\Tenant;
use FluxErp\Models\VatRate;
use FluxErp\Settings\ProductSettings;
use Livewire\Livewire;

beforeEach(function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();
});

/**
 * Build a variant whose inheritable columns all match the parent, so the override
 * bookkeeping on save marks exactly the fields a test sets and nothing else. Without
 * the alignment the factory's random values count as overrides on their own.
 */
function inheritingVariant(ProductModel $parent, array $overrides = []): ProductModel
{
    $raw = $parent->getRawOriginal();
    $aligned = collect($parent->getInheritableFields())
        ->filter(fn (string $field): bool => array_key_exists($field, $raw))
        ->mapWithKeys(fn (string $field): array => [$field => $raw[$field]])
        ->all();

    return ProductModel::factory()->create(
        array_merge($aligned, ['parent_id' => $parent->getKey()], $overrides)
    );
}

test('resetFields clears overridden_fields on the variant', function (): void {
    $parent = ProductModel::factory()->create(['name' => 'Parent']);
    $variant = inheritingVariant($parent, ['name' => 'override']);

    expect($variant->fresh()->overridden_fields)->toBe(['name']);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->call('resetFields', 'name')
        ->assertHasNoErrors();

    expect($variant->fresh()->overridden_fields)->toBeNull();
});

test('resetFields on a non-inheritable field surfaces a validation error', function (): void {
    $parent = ProductModel::factory()->create();
    $variant = ProductModel::factory()->create(['parent_id' => $parent->getKey()]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->call('resetFields', 'product_number')
        ->assertHasErrors(['fields.0']);
});

test('resetRelations deletes own pivot rows for the relation', function (): void {
    $cat = Category::factory()->create([
        'model_type' => morph_alias(ProductModel::class),
    ]);
    $parent = ProductModel::factory()->create();
    $variant = ProductModel::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownCategories()->attach([$cat->getKey()]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->call('resetRelations', 'categories')
        ->assertHasNoErrors();

    expect($variant->ownCategories()->count())->toBe(0);
});

test('resetFields on the parent clears the field across every variant', function (): void {
    $parent = ProductModel::factory()->create(['name' => 'Parent']);
    inheritingVariant($parent, ['name' => 'first override']);
    inheritingVariant($parent, ['name' => 'second override', 'description' => 'differs']);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->call('resetFields', 'name')
        ->assertHasNoErrors();

    foreach (ProductModel::where('parent_id', $parent->getKey())->get() as $variant) {
        expect($variant->overridden_fields ?? [])->not->toContain('name');
    }
});

test('promoteToStandalone clears is_variant_parent on a parent without active children', function (): void {
    $parent = ProductModel::factory()->create([
        'parent_id' => null,
        'is_variant_parent' => true,
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'is_active' => false,
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->call('promoteToStandalone')
        ->assertHasNoErrors();

    expect($parent->fresh()->is_variant_parent)->toBeFalse();
});

test('promoteToStandalone surfaces error when active children still exist', function (): void {
    $parent = ProductModel::factory()->create([
        'parent_id' => null,
        'is_variant_parent' => true,
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'is_active' => true,
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->call('promoteToStandalone')
        ->assertHasErrors(['is_variant_parent']);
});

test('variant edit form renders inheritance indicator on inheritable fields', function (): void {
    $parent = ProductModel::factory()->create();
    $variant = ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
    ]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->assertSeeHtml(__('Inherited'));
});

test('non-variant edit form does not render inheritance indicator chrome', function (): void {
    $product = ProductModel::factory()->create(['parent_id' => null]);

    Livewire::test(Product::class, ['id' => $product->getKey()])
        ->assertDontSeeHtml(__('Inherited'))
        ->assertDontSeeHtml(__('Overridden'));
});

test('priceLists payload marks variant_owns_price true when variant has own price', function (): void {
    $listA = FluxErp\Models\PriceList::factory()->create(['is_default' => false]);
    $parent = ProductModel::factory()->create();
    FluxErp\Models\Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => '10.0000',
    ]);
    $variant = ProductModel::factory()->create(['parent_id' => $parent->getKey()]);
    FluxErp\Models\Price::factory()->create([
        'product_id' => $variant->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => '15.0000',
    ]);

    $component = Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->call('getPriceLists');

    $listEntry = collect($component->get('priceLists'))->firstWhere('id', $listA->getKey());

    expect($listEntry['variant_owns_price'])->toBeTrue();
});

test('priceLists payload marks variant_owns_price false when variant inherits from parent product', function (): void {
    $listA = FluxErp\Models\PriceList::factory()->create(['is_default' => false]);
    $parent = ProductModel::factory()->create();
    FluxErp\Models\Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => '10.0000',
    ]);
    $variant = ProductModel::factory()->create(['parent_id' => $parent->getKey()]);

    $component = Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->call('getPriceLists');

    $listEntry = collect($component->get('priceLists'))->firstWhere('id', $listA->getKey());

    expect($listEntry['variant_owns_price'])->toBeFalse();
});

test('priceLists payload marks variant_owns_price false on non-variant products', function (): void {
    $listA = FluxErp\Models\PriceList::factory()->create(['is_default' => false]);
    $product = ProductModel::factory()->create();
    FluxErp\Models\Price::factory()->create([
        'product_id' => $product->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => '10.0000',
    ]);

    $component = Livewire::test(Product::class, ['id' => $product->getKey()])
        ->call('getPriceLists');

    $listEntry = collect($component->get('priceLists'))->firstWhere('id', $listA->getKey());

    expect($listEntry['variant_owns_price'])->toBeFalse();
});

test('variant prices tab shows the inherited badge for inherited price lists', function (): void {
    $listA = FluxErp\Models\PriceList::factory()->create(['is_default' => false, 'name' => 'Liste A']);
    $parent = ProductModel::factory()->create();
    FluxErp\Models\Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => '10.0000',
    ]);
    $variant = ProductModel::factory()->create(['parent_id' => $parent->getKey()]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->assertSeeHtml(__('Inherited'));
});

test('parent product computes inheritance counters per inheritable field', function (): void {
    $parent = ProductModel::factory()->create();
    inheritingVariant($parent, ['name' => 'override']);
    inheritingVariant($parent);

    $component = Livewire::test(Product::class, ['id' => $parent->getKey()]);

    $counters = $component->instance()->inheritanceCounters;

    expect($counters['name']['inheriting'])->toBe(1);
    expect($counters['name']['total'])->toBe(2);
    expect($counters['description']['inheriting'])->toBe(2);
    expect($counters['description']['total'])->toBe(2);
});

test('inheritanceCounters is empty for non-parent products', function (): void {
    $product = ProductModel::factory()->create([
        'parent_id' => null,
        'is_variant_parent' => false,
    ]);

    $component = Livewire::test(Product::class, ['id' => $product->getKey()]);

    expect($component->instance()->inheritanceCounters)->toBe([]);
});

test('variant bulk-reset panel renders on parent product edit view', function (): void {
    $parent = ProductModel::factory()->create();
    inheritingVariant($parent, ['name' => 'override']);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->assertSeeHtml(__('Effect on variants'));
});

test('variant bulk-reset panel does not render on non-parent products', function (): void {
    $product = ProductModel::factory()->create([
        'parent_id' => null,
        'is_variant_parent' => false,
    ]);

    Livewire::test(Product::class, ['id' => $product->getKey()])
        ->assertDontSeeHtml(__('Effect on variants'));
});

test('variant header shows consistency badge when there are field overrides', function (): void {
    $parent = ProductModel::factory()->create();
    $variant = inheritingVariant($parent, [
        'name' => 'override',
        'description' => 'differs',
    ]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->assertSeeHtml(__(':fields fields overridden, :prices prices differing', ['fields' => 2, 'prices' => 0]));
});

test('variant header shows price-override count in consistency badge', function (): void {
    $listA = FluxErp\Models\PriceList::factory()->create(['is_default' => false]);
    $parent = ProductModel::factory()->create();
    $variant = inheritingVariant($parent);
    FluxErp\Models\Price::factory()->create([
        'product_id' => $variant->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => '15.0000',
    ]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->assertSeeHtml(__(':fields fields overridden, :prices prices differing', ['fields' => 0, 'prices' => 1]));
});

test('inheritanceState returns null on non-variant products', function (): void {
    $product = ProductModel::factory()->create(['parent_id' => null]);

    $component = Livewire::test(Product::class, ['id' => $product->getKey()]);

    expect($component->instance()->inheritanceState)->toBeNull();
});

test('inheritanceState returns null on variants with no overrides', function (): void {
    $parent = ProductModel::factory()->create();
    $variant = inheritingVariant($parent);

    $component = Livewire::test(Product::class, ['id' => $variant->getKey()]);

    expect($component->instance()->inheritanceState)->toBeNull();
});

test('variant list filters to only overridden variants when toggle is on', function (): void {
    $parent = ProductModel::factory()->create();
    inheritingVariant($parent, ['name' => 'override']);
    inheritingVariant($parent);

    $form = new FluxErp\Livewire\Forms\ProductForm(
        Livewire::new(FluxErp\Livewire\Product\VariantList::class),
        'product'
    );
    $form->fill($parent);

    $component = Livewire::test(FluxErp\Livewire\Product\VariantList::class, ['product' => $form]);

    $reflection = new ReflectionMethod($component->instance(), 'getBuilder');
    $reflection->setAccessible(true);

    $countAll = $reflection->invoke(
        $component->instance(),
        ProductModel::query()->where('parent_id', $parent->getKey())
    )->count();
    expect($countAll)->toBe(2);

    $component->set('onlyOverrides', true);

    $countOverrides = $reflection->invoke(
        $component->instance(),
        ProductModel::query()->where('parent_id', $parent->getKey())
    )->count();
    expect($countOverrides)->toBe(1);
});

test('variant list eager-loads parent so accessor lookups do not N+1', function (): void {
    $parent = ProductModel::factory()->create();
    inheritingVariant($parent);
    inheritingVariant($parent);

    $form = new FluxErp\Livewire\Forms\ProductForm(
        Livewire::new(FluxErp\Livewire\Product\VariantList::class),
        'product'
    );
    $form->fill($parent);

    $component = Livewire::test(FluxErp\Livewire\Product\VariantList::class, ['product' => $form]);

    $reflection = new ReflectionMethod($component->instance(), 'getBuilder');
    $reflection->setAccessible(true);
    $builder = $reflection->invoke($component->instance(), ProductModel::query());

    expect($builder->getEagerLoads())->toHaveKey('parent');
});

test('orphaned-parent banner shows on parent that lost all active variants', function (): void {
    $parent = ProductModel::factory()->create([
        'parent_id' => null,
        'is_variant_parent' => true,
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'is_active' => false,
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->assertSeeHtml(__('This product had variants, none of them is active anymore.'))
        ->assertSeeHtml(__('Activate as a standalone product'))
        ->assertSeeHtml(__('Deactivate product'))
        ->assertSeeHtml(__('Create a new variant'));
});

test('orphaned-parent banner does not show when active children exist', function (): void {
    $parent = ProductModel::factory()->create([
        'is_variant_parent' => true,
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'is_active' => true,
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->assertDontSeeHtml(__('This product had variants, none of them is active anymore.'));
});

test('orphaned-parent banner does not show on standalone products', function (): void {
    $product = ProductModel::factory()->create([
        'parent_id' => null,
        'is_variant_parent' => false,
    ]);

    Livewire::test(Product::class, ['id' => $product->getKey()])
        ->assertDontSeeHtml(__('This product had variants, none of them is active anymore.'));
});

test('isOrphanedParent computed returns true when is_variant_parent and no active children', function (): void {
    $parent = ProductModel::factory()->create([
        'is_variant_parent' => true,
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'is_active' => false,
    ]);

    $component = Livewire::test(Product::class, ['id' => $parent->getKey()]);

    expect($component->instance()->isOrphanedParent)->toBeTrue();
});

test('deactivate banner action persists is_active false', function (): void {
    $parent = ProductModel::factory()
        ->for(VatRate::default())
        ->create([
            'parent_id' => null,
            'is_variant_parent' => true,
            'is_active' => true,
            'is_bundle' => false,
        ]);
    $parent->tenants()->attach(Tenant::default()->getKey());

    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'is_active' => false,
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->call('deactivate')
        ->assertReturned(true);

    expect($parent->fresh()->is_active)->toBeFalse();
});
