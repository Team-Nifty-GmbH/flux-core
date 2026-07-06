<?php

use FluxErp\Models\Product;
use FluxErp\Support\VariantInheritance\InheritanceSync;

test('changedInheritableFields returns only dirtied inheritable columns', function (): void {
    $parent = Product::factory()->make(['name' => 'A', 'weight_gram' => 100]);
    $parent->syncOriginal();
    $parent->name = 'B';                 // inheritable
    $parent->product_number = 'X-1';     // NOT inheritable

    expect(InheritanceSync::changedInheritableFields($parent))->toBe(['name']);
});
