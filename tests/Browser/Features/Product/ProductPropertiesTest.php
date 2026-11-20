<?php

use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Models\Language;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Models\VatRate;

test('a property added after saving does not drop the existing ones', function (): void {
    Language::factory()->create(['is_default' => true]);
    PriceList::factory()->create(['is_default' => true, 'is_net' => true]);

    $product = Product::factory()
        ->for(VatRate::factory()->create(['is_default' => true]))
        ->create(['is_bundle' => false]);
    $product->tenants()->attach($this->dbTenant->getKey());

    $group = ProductPropertyGroup::query()->create(['name' => 'Reiseart']);
    $existing = collect(['Trekking', 'Kultur', 'Nepal'])
        ->map(fn (string $name) => ProductProperty::factory()->create([
            'name' => $name,
            'product_property_group_id' => $group->getKey(),
            'property_type_enum' => PropertyTypeEnum::Option,
        ]));
    $product->productProperties()->sync($existing->map->getKey()->all());

    $added = ProductProperty::factory()->create([
        'name' => 'Wandern',
        'product_property_group_id' => $group->getKey(),
        'property_type_enum' => PropertyTypeEnum::Option,
    ]);

    $page = visit(route('products.id', ['id' => $product->getKey()]))
        ->assertNoSmoke();

    $wire = "Livewire.all().find((component) => component.name === 'product.product').\$wire";

    $page->script("() => {$wire}.save()");
    $page->script("() => {$wire}.showProductPropertiesModal()");
    $page->script("() => {$wire}.loadProductProperties({$group->getKey()})");
    $page->click('#product-property' . $added->getKey());
    $page->script("() => {$wire}.addProductProperties()");
    $page->script("() => {$wire}.save()");

    expect($product->productProperties()->pluck('name')->sort()->values()->all())
        ->toBe(['Kultur', 'Nepal', 'Trekking', 'Wandern']);
});
