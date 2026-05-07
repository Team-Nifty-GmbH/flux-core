<?php

use FluxErp\Models\Product;
use FluxErp\Rules\ProductHierarchyDepth;
use Illuminate\Support\Facades\Validator;

it('passes when parent_id refers to a top-level product', function (): void {
    $topLevel = Product::factory()->create(['parent_id' => null]);

    $validator = Validator::make(
        ['parent_id' => $topLevel->getKey()],
        ['parent_id' => [new ProductHierarchyDepth()]]
    );

    expect($validator->fails())->toBeFalse();
});

it('fails when parent_id refers to a variant (would create depth 3)', function (): void {
    $top = Product::factory()->create(['parent_id' => null]);
    $variant = Product::factory()->create(['parent_id' => $top->getKey()]);

    $validator = Validator::make(
        ['parent_id' => $variant->getKey()],
        ['parent_id' => [new ProductHierarchyDepth()]]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('parent_id'))->toContain('variant');
});

it('passes when parent_id is null (standalone or unchanged)', function (): void {
    $validator = Validator::make(
        ['parent_id' => null],
        ['parent_id' => [new ProductHierarchyDepth()]]
    );

    expect($validator->fails())->toBeFalse();
});

it('passes when parent_id refers to a non-existent product (existence checked elsewhere)', function (): void {
    $validator = Validator::make(
        ['parent_id' => 9999999],
        ['parent_id' => [new ProductHierarchyDepth()]]
    );

    expect($validator->fails())->toBeFalse();
});

it('UpdateProduct rejects setting parent_id when product already has children', function (): void {
    $existingParent = Product::factory()->create();
    Product::factory()->create(['parent_id' => $existingParent->getKey()]);
    $newTop = Product::factory()->create();

    expect(fn () => FluxErp\Actions\Product\UpdateProduct::make([
        'id' => $existingParent->getKey(),
        'parent_id' => $newTop->getKey(),
    ])->validate()->execute())
        ->toThrow(Illuminate\Validation\ValidationException::class);
});
