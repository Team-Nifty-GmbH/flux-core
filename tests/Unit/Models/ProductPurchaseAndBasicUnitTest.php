<?php

use FluxErp\Models\Product;
use FluxErp\Models\Unit;

test('purchase unit and reference unit are related', function (): void {
    $purchaseUnit = Unit::factory()->create();
    $referenceUnit = Unit::factory()->create();

    $product = Product::factory()->create([
        'purchase_unit_id' => $purchaseUnit->getKey(),
        'reference_unit_id' => $referenceUnit->getKey(),
    ]);

    expect($product->purchaseUnit->getKey())->toBe($purchaseUnit->getKey())
        ->and($product->referenceUnit->getKey())->toBe($referenceUnit->getKey());
});
