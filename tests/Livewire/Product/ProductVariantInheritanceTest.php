<?php

use FluxErp\Livewire\Product\Product;
use FluxErp\Models\Category;
use FluxErp\Models\Product as ProductModel;
use FluxErp\Models\Tenant;
use Livewire\Livewire;

beforeEach(function (): void {
    Tenant::default()->update(['product_variant_inheritance_enabled' => true]);
    Tenant::clearDefaultCache();
});

test('resetField clears overridden_fields on the variant', function (): void {
    $parent = ProductModel::factory()->create(['name' => 'Parent']);
    $variant = ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
        'name' => 'override',
    ]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->call('resetField', 'name')
        ->assertHasNoErrors();

    expect($variant->fresh()->overridden_fields)->toBeNull();
});

test('resetField on non-inheritable field surfaces a validation error', function (): void {
    $parent = ProductModel::factory()->create();
    $variant = ProductModel::factory()->create(['parent_id' => $parent->getKey()]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->call('resetField', 'product_number')
        ->assertHasErrors(['inheritance']);
});

test('resetRelation deletes own pivot rows for the relation', function (): void {
    $cat = Category::factory()->create([
        'model_type' => morph_alias(ProductModel::class),
    ]);
    $parent = ProductModel::factory()->create();
    $variant = ProductModel::factory()->create(['parent_id' => $parent->getKey()]);
    $variant->ownCategories()->attach([$cat->getKey()]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->call('resetRelation', 'categories')
        ->assertHasNoErrors();

    expect($variant->ownCategories()->count())->toBe(0);
});

test('resetFieldOnAllVariants clears the field across every variant', function (): void {
    $parent = ProductModel::factory()->create(['name' => 'Parent']);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name', 'description'],
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->call('resetFieldOnAllVariants', 'name')
        ->assertHasNoErrors();

    foreach (ProductModel::where('parent_id', $parent->getKey())->get() as $variant) {
        expect($variant->overridden_fields ?? [])->not->toContain('name');
    }
});

test('promoteToStandalone clears was_parent on a parent without active children', function (): void {
    $parent = ProductModel::factory()->create([
        'parent_id' => null,
        'was_parent' => true,
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'is_active' => false,
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->call('promoteToStandalone')
        ->assertHasNoErrors();

    expect($parent->fresh()->was_parent)->toBeFalse();
});

test('promoteToStandalone surfaces error when active children still exist', function (): void {
    $parent = ProductModel::factory()->create([
        'parent_id' => null,
        'was_parent' => true,
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'is_active' => true,
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->call('promoteToStandalone')
        ->assertHasErrors(['inheritance']);
});

test('variant edit form renders inheritance indicator on inheritable fields', function (): void {
    $parent = ProductModel::factory()->create();
    $variant = ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
    ]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->assertSeeHtml('Vererbt');
});

test('non-variant edit form does not render inheritance indicator chrome', function (): void {
    $product = ProductModel::factory()->create(['parent_id' => null]);

    Livewire::test(Product::class, ['id' => $product->getKey()])
        ->assertDontSeeHtml('Vererbt')
        ->assertDontSeeHtml('Überschrieben');
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

test('variant prices tab shows Vererbt badge for inherited price-lists', function (): void {
    $listA = FluxErp\Models\PriceList::factory()->create(['is_default' => false, 'name' => 'Liste A']);
    $parent = ProductModel::factory()->create();
    FluxErp\Models\Price::factory()->create([
        'product_id' => $parent->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => '10.0000',
    ]);
    $variant = ProductModel::factory()->create(['parent_id' => $parent->getKey()]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->assertSeeHtml('Vererbt');
});

test('parent product computes inheritance counters per inheritable field', function (): void {
    $parent = ProductModel::factory()->create();
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);

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
        'was_parent' => false,
    ]);

    $component = Livewire::test(Product::class, ['id' => $product->getKey()]);

    expect($component->instance()->inheritanceCounters)->toBe([]);
});

test('variant bulk-reset panel renders on parent product edit view', function (): void {
    $parent = ProductModel::factory()->create();
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);

    Livewire::test(Product::class, ['id' => $parent->getKey()])
        ->assertSeeHtml('Auswirkung auf Varianten');
});

test('variant bulk-reset panel does not render on non-parent products', function (): void {
    $product = ProductModel::factory()->create([
        'parent_id' => null,
        'was_parent' => false,
    ]);

    Livewire::test(Product::class, ['id' => $product->getKey()])
        ->assertDontSeeHtml('Auswirkung auf Varianten');
});

test('variant header shows consistency badge when there are field overrides', function (): void {
    $parent = ProductModel::factory()->create();
    $variant = ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name', 'description'],
    ]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->assertSeeHtml('2 Felder überschrieben');
});

test('variant header shows price-override count in consistency badge', function (): void {
    $listA = FluxErp\Models\PriceList::factory()->create(['is_default' => false]);
    $parent = ProductModel::factory()->create();
    $variant = ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);
    FluxErp\Models\Price::factory()->create([
        'product_id' => $variant->getKey(),
        'price_list_id' => $listA->getKey(),
        'price' => '15.0000',
    ]);

    Livewire::test(Product::class, ['id' => $variant->getKey()])
        ->assertSeeHtml('1 Preise abweichend');
});

test('inheritanceState returns null on non-variant products', function (): void {
    $product = ProductModel::factory()->create(['parent_id' => null]);

    $component = Livewire::test(Product::class, ['id' => $product->getKey()]);

    expect($component->instance()->inheritanceState)->toBeNull();
});

test('inheritanceState returns null on variants with no overrides', function (): void {
    $parent = ProductModel::factory()->create();
    $variant = ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);

    $component = Livewire::test(Product::class, ['id' => $variant->getKey()]);

    expect($component->instance()->inheritanceState)->toBeNull();
});

test('variant list filters to only overridden variants when toggle is on', function (): void {
    $parent = ProductModel::factory()->create();
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);

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
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);
    ProductModel::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);

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
