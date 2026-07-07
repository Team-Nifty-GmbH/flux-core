<?php

use FluxErp\Actions\Product\UpdateProduct;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\Product;
use FluxErp\Models\ProductProperty;
use FluxErp\Settings\ProductSettings;

beforeEach(function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $this->parent = Product::factory()->create();
    $this->child1 = Product::factory()->create(['parent_id' => $this->parent->getKey()]);
    $this->child2 = Product::factory()->create(['parent_id' => $this->parent->getKey()]);
});

// categories

test('parent adding a category propagates an inherited copy to non-owning children', function (): void {
    $category = Category::factory()->create(['model_type' => morph_alias(Product::class)]);

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'categories' => [$category->getKey()],
    ])->validate()->execute();

    foreach ([$this->child1, $this->child2] as $child) {
        $row = DB::table('categorizable')
            ->where('categorizable_id', $child->getKey())
            ->where('categorizable_type', morph_alias(Product::class))
            ->where('category_id', $category->getKey())
            ->first();

        expect($row)->not->toBeNull()
            ->and((bool) $row->is_inherited)->toBeTrue();
    }
});

test('parent removing a category removes the inherited child copies', function (): void {
    $category = Category::factory()->create(['model_type' => morph_alias(Product::class)]);

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'categories' => [$category->getKey()],
    ])->validate()->execute();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'categories' => [],
    ])->validate()->execute();

    foreach ([$this->child1, $this->child2] as $child) {
        $row = DB::table('categorizable')
            ->where('categorizable_id', $child->getKey())
            ->where('categorizable_type', morph_alias(Product::class))
            ->where('category_id', $category->getKey())
            ->first();

        expect($row)->toBeNull();
    }
});

test('a child\'s own category assignment is left untouched by parent propagation', function (): void {
    $category = Category::factory()->create(['model_type' => morph_alias(Product::class)]);

    $this->child2->ownCategories()->attach([$category->getKey()]);

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'categories' => [$category->getKey()],
    ])->validate()->execute();

    $child2Row = DB::table('categorizable')
        ->where('categorizable_id', $this->child2->getKey())
        ->where('categorizable_type', morph_alias(Product::class))
        ->where('category_id', $category->getKey())
        ->first();

    expect((bool) $child2Row->is_inherited)->toBeFalse();

    $child1Row = DB::table('categorizable')
        ->where('categorizable_id', $this->child1->getKey())
        ->where('categorizable_type', morph_alias(Product::class))
        ->where('category_id', $category->getKey())
        ->first();

    expect((bool) $child1Row->is_inherited)->toBeTrue();
});

// suppliers

test('parent adding a supplier propagates an inherited copy to non-owning children', function (): void {
    $contact = Contact::factory()->create();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'suppliers' => [['contact_id' => $contact->getKey()]],
    ])->validate()->execute();

    foreach ([$this->child1, $this->child2] as $child) {
        $row = DB::table('product_supplier')
            ->where('product_id', $child->getKey())
            ->where('contact_id', $contact->getKey())
            ->first();

        expect($row)->not->toBeNull()
            ->and((bool) $row->is_inherited)->toBeTrue();
    }
});

test('parent removing a supplier removes the inherited child copies', function (): void {
    $contact = Contact::factory()->create();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'suppliers' => [['contact_id' => $contact->getKey()]],
    ])->validate()->execute();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'suppliers' => [],
    ])->validate()->execute();

    foreach ([$this->child1, $this->child2] as $child) {
        $row = DB::table('product_supplier')
            ->where('product_id', $child->getKey())
            ->where('contact_id', $contact->getKey())
            ->first();

        expect($row)->toBeNull();
    }
});

test('a child\'s own supplier assignment is left untouched by parent propagation', function (): void {
    $contact = Contact::factory()->create();

    $this->child2->ownSuppliers()->attach([$contact->getKey()]);

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'suppliers' => [['contact_id' => $contact->getKey()]],
    ])->validate()->execute();

    $child2Row = DB::table('product_supplier')
        ->where('product_id', $this->child2->getKey())
        ->where('contact_id', $contact->getKey())
        ->first();

    expect((bool) $child2Row->is_inherited)->toBeFalse();

    $child1Row = DB::table('product_supplier')
        ->where('product_id', $this->child1->getKey())
        ->where('contact_id', $contact->getKey())
        ->first();

    expect((bool) $child1Row->is_inherited)->toBeTrue();
});

// productProperties

test('parent adding a product property propagates an inherited copy with value to non-owning children', function (): void {
    $property = ProductProperty::factory()->create();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'product_properties' => [['id' => $property->getKey(), 'value' => 'red']],
    ])->validate()->execute();

    foreach ([$this->child1, $this->child2] as $child) {
        $row = DB::table('product_product_property')
            ->where('product_id', $child->getKey())
            ->where('product_property_id', $property->getKey())
            ->first();

        expect($row)->not->toBeNull()
            ->and((bool) $row->is_inherited)->toBeTrue()
            ->and($row->value)->toBe('red');
    }
});

test('parent updating a product property value refreshes inherited child copies', function (): void {
    $property = ProductProperty::factory()->create();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'product_properties' => [['id' => $property->getKey(), 'value' => 'red']],
    ])->validate()->execute();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'product_properties' => [['id' => $property->getKey(), 'value' => 'blue']],
    ])->validate()->execute();

    foreach ([$this->child1, $this->child2] as $child) {
        $row = DB::table('product_product_property')
            ->where('product_id', $child->getKey())
            ->where('product_property_id', $property->getKey())
            ->first();

        expect($row->value)->toBe('blue');
    }
});

test('parent removing a product property removes the inherited child copies', function (): void {
    $property = ProductProperty::factory()->create();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'product_properties' => [['id' => $property->getKey(), 'value' => 'red']],
    ])->validate()->execute();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'product_properties' => [],
    ])->validate()->execute();

    foreach ([$this->child1, $this->child2] as $child) {
        $row = DB::table('product_product_property')
            ->where('product_id', $child->getKey())
            ->where('product_property_id', $property->getKey())
            ->first();

        expect($row)->toBeNull();
    }
});

test('a child\'s own product property value is left untouched by parent propagation', function (): void {
    $property = ProductProperty::factory()->create();

    $this->child2->ownProductProperties()->attach([$property->getKey() => ['value' => 'green']]);

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'product_properties' => [['id' => $property->getKey(), 'value' => 'red']],
    ])->validate()->execute();

    $child2Row = DB::table('product_product_property')
        ->where('product_id', $this->child2->getKey())
        ->where('product_property_id', $property->getKey())
        ->first();

    expect((bool) $child2Row->is_inherited)->toBeFalse()
        ->and($child2Row->value)->toBe('green');

    $child1Row = DB::table('product_product_property')
        ->where('product_id', $this->child1->getKey())
        ->where('product_property_id', $property->getKey())
        ->first();

    expect((bool) $child1Row->is_inherited)->toBeTrue()
        ->and($child1Row->value)->toBe('red');
});
