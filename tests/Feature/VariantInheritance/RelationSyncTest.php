<?php

use FluxErp\Actions\Product\UpdateProduct;
use FluxErp\Models\Category;
use FluxErp\Models\Contact;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductProperty;

beforeEach(function (): void {
    $this->list = PriceList::factory()->create(['is_default' => false]);
    $this->parent = Product::factory()->create();
    $this->child1 = Product::factory()->create(['parent_id' => $this->parent->getKey()]);
    $this->child2 = Product::factory()->create(['parent_id' => $this->parent->getKey()]);
});

// prices

test('a variant editing its price takes ownership of the inherited copy', function (): void {
    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'prices' => [['price_list_id' => $this->list->getKey(), 'price' => '100.0000']],
    ])->validate()->execute();

    UpdateProduct::make([
        'id' => $this->child1->getKey(),
        'prices' => [['price_list_id' => $this->list->getKey(), 'price' => '150.0000']],
    ])->validate()->execute();

    $rows = DB::table('prices')
        ->where('product_id', $this->child1->getKey())
        ->where('price_list_id', $this->list->getKey())
        ->whereNull('deleted_at')
        ->get();

    expect($rows)->toHaveCount(1)
        ->and((bool) $rows->first()->is_inherited)->toBeFalse()
        ->and((float) $rows->first()->price)->toBe(150.0);
});

test('a subsequent parent price change does not overwrite the variant\'s now-owned price', function (): void {
    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'prices' => [['price_list_id' => $this->list->getKey(), 'price' => '100.0000']],
    ])->validate()->execute();

    UpdateProduct::make([
        'id' => $this->child1->getKey(),
        'prices' => [['price_list_id' => $this->list->getKey(), 'price' => '150.0000']],
    ])->validate()->execute();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'prices' => [['price_list_id' => $this->list->getKey(), 'price' => '999.0000']],
    ])->validate()->execute();

    $child1Row = DB::table('prices')
        ->where('product_id', $this->child1->getKey())
        ->where('price_list_id', $this->list->getKey())
        ->whereNull('deleted_at')
        ->first();

    expect((float) $child1Row->price)->toBe(150.0)
        ->and((bool) $child1Row->is_inherited)->toBeFalse();

    $child2Row = DB::table('prices')
        ->where('product_id', $this->child2->getKey())
        ->where('price_list_id', $this->list->getKey())
        ->whereNull('deleted_at')
        ->first();

    expect((float) $child2Row->price)->toBe(999.0)
        ->and((bool) $child2Row->is_inherited)->toBeTrue();
});

test('a variant\'s inherited price for an unrelated list is not deleted just because it is absent from the payload', function (): void {
    $otherList = PriceList::factory()->create(['is_default' => false]);

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'prices' => [
            ['price_list_id' => $this->list->getKey(), 'price' => '100.0000'],
            ['price_list_id' => $otherList->getKey(), 'price' => '20.0000'],
        ],
    ])->validate()->execute();

    // child edits only $this->list, leaving $otherList's inherited copy out of the payload
    UpdateProduct::make([
        'id' => $this->child1->getKey(),
        'prices' => [['price_list_id' => $this->list->getKey(), 'price' => '150.0000']],
    ])->validate()->execute();

    $otherListRow = DB::table('prices')
        ->where('product_id', $this->child1->getKey())
        ->where('price_list_id', $otherList->getKey())
        ->whereNull('deleted_at')
        ->first();

    expect($otherListRow)->not->toBeNull()
        ->and((bool) $otherListRow->is_inherited)->toBeTrue()
        ->and((float) $otherListRow->price)->toBe(20.0);
});

// pivots (product properties)

test('a variant editing its own product property takes ownership of the inherited copy', function (): void {
    $property = ProductProperty::factory()->create();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'product_properties' => [['id' => $property->getKey(), 'value' => 'red']],
    ])->validate()->execute();

    UpdateProduct::make([
        'id' => $this->child1->getKey(),
        'product_properties' => [['id' => $property->getKey(), 'value' => 'blue']],
    ])->validate()->execute();

    $rows = DB::table('product_product_property')
        ->where('product_id', $this->child1->getKey())
        ->where('product_property_id', $property->getKey())
        ->get();

    expect($rows)->toHaveCount(1)
        ->and((bool) $rows->first()->is_inherited)->toBeFalse()
        ->and($rows->first()->value)->toBe('blue');

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'product_properties' => [['id' => $property->getKey(), 'value' => 'green']],
    ])->validate()->execute();

    $child1Row = DB::table('product_product_property')
        ->where('product_id', $this->child1->getKey())
        ->where('product_property_id', $property->getKey())
        ->first();

    expect($child1Row->value)->toBe('blue')
        ->and((bool) $child1Row->is_inherited)->toBeFalse();

    $child2Row = DB::table('product_product_property')
        ->where('product_id', $this->child2->getKey())
        ->where('product_property_id', $property->getKey())
        ->first();

    expect($child2Row->value)->toBe('green')
        ->and((bool) $child2Row->is_inherited)->toBeTrue();
});

test('a variant setting its own supplier takes ownership of the inherited copy without wiping other suppliers', function (): void {
    $contact = Contact::factory()->create();
    $otherContact = Contact::factory()->create();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'suppliers' => [
            ['contact_id' => $contact->getKey()],
            ['contact_id' => $otherContact->getKey()],
        ],
    ])->validate()->execute();

    UpdateProduct::make([
        'id' => $this->child1->getKey(),
        'suppliers' => [['contact_id' => $contact->getKey()]],
    ])->validate()->execute();

    $ownedRow = DB::table('product_supplier')
        ->where('product_id', $this->child1->getKey())
        ->where('contact_id', $contact->getKey())
        ->get();

    expect($ownedRow)->toHaveCount(1)
        ->and((bool) $ownedRow->first()->is_inherited)->toBeFalse();

    // the inherited copy for the other supplier must survive, since it was absent
    // from the payload, not explicitly removed.
    $untouchedRow = DB::table('product_supplier')
        ->where('product_id', $this->child1->getKey())
        ->where('contact_id', $otherContact->getKey())
        ->first();

    expect($untouchedRow)->not->toBeNull()
        ->and((bool) $untouchedRow->is_inherited)->toBeTrue();
});

test('a parent keeps its suppliers across successive updates', function (): void {
    $contact = Contact::factory()->create();
    $otherContact = Contact::factory()->create();

    $this->parent->ownSuppliers()->attach([$contact->getKey(), $otherContact->getKey()]);

    // an unrelated field update must not touch suppliers, since the payload omits the key entirely
    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'name' => 'renamed parent',
    ])->validate()->execute();

    expect(
        DB::table('product_supplier')->where('product_id', $this->parent->getKey())->pluck('contact_id')->sort()->values()->all()
    )->toBe([$contact->getKey(), $otherContact->getKey()]);

    // re-sending the full existing set must not wipe or duplicate rows (regression for the
    // sync() list-index-vs-contact_id keying bug)
    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'suppliers' => [
            ['contact_id' => $contact->getKey()],
            ['contact_id' => $otherContact->getKey()],
        ],
    ])->validate()->execute();

    $rows = DB::table('product_supplier')->where('product_id', $this->parent->getKey())->get();

    expect($rows)->toHaveCount(2)
        ->and($rows->pluck('contact_id')->sort()->values()->all())->toBe([$contact->getKey(), $otherContact->getKey()]);
});

test('a variant setting its own category takes ownership of the inherited copy', function (): void {
    $category = Category::factory()->create(['model_type' => morph_alias(Product::class)]);

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'categories' => [$category->getKey()],
    ])->validate()->execute();

    UpdateProduct::make([
        'id' => $this->child1->getKey(),
        'categories' => [$category->getKey()],
    ])->validate()->execute();

    $row = DB::table('categorizable')
        ->where('categorizable_id', $this->child1->getKey())
        ->where('categorizable_type', morph_alias(Product::class))
        ->where('category_id', $category->getKey())
        ->first();

    expect($row)->not->toBeNull()
        ->and((bool) $row->is_inherited)->toBeFalse();

    UpdateProduct::make([
        'id' => $this->parent->getKey(),
        'categories' => [],
    ])->validate()->execute();

    $rowAfterParentRemoval = DB::table('categorizable')
        ->where('categorizable_id', $this->child1->getKey())
        ->where('categorizable_type', morph_alias(Product::class))
        ->where('category_id', $category->getKey())
        ->first();

    expect($rowAfterParentRemoval)->not->toBeNull()
        ->and((bool) $rowAfterParentRemoval->is_inherited)->toBeFalse();
});
