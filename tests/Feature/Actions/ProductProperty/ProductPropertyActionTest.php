<?php

use FluxErp\Actions\ProductProperty\CreateProductProperty;
use FluxErp\Actions\ProductProperty\DeleteProductProperty;
use FluxErp\Actions\ProductProperty\UpdateProductProperty;
use FluxErp\Models\ProductProperty;

test('create product property', function (): void {
    $prop = CreateProductProperty::make([
        'name' => 'Weight',
        'property_type_enum' => 'text',
    ])->validate()->execute();

    expect($prop)->toBeInstanceOf(ProductProperty::class)
        ->name->toBe('Weight');
});

test('create product property requires name and type', function (): void {
    CreateProductProperty::assertValidationErrors([], ['name', 'property_type_enum']);
});

test('update product property', function (): void {
    $prop = ProductProperty::factory()->create();

    $updated = UpdateProductProperty::make([
        'id' => $prop->getKey(),
        'name' => 'Dimensions',
    ])->validate()->execute();

    expect($updated->name)->toBe('Dimensions');
});

test('delete product property', function (): void {
    $prop = ProductProperty::factory()->create();

    expect(DeleteProductProperty::make(['id' => $prop->getKey()])
        ->validate()->execute())->toBeTrue();
});

test('update product property moves it to another group', function (): void {
    $from = FluxErp\Actions\ProductPropertyGroup\CreateProductPropertyGroup::make(['name' => 'From'])
        ->validate()
        ->execute();
    $to = FluxErp\Actions\ProductPropertyGroup\CreateProductPropertyGroup::make(['name' => 'To'])
        ->validate()
        ->execute();
    $prop = ProductProperty::factory()->create(['product_property_group_id' => $from->getKey()]);

    UpdateProductProperty::make([
        'id' => $prop->getKey(),
        'product_property_group_id' => $to->getKey(),
    ])->validate()->execute();

    expect($prop->refresh()->product_property_group_id)->toBe($to->getKey());
});
