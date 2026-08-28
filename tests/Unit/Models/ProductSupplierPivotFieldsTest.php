<?php

use FluxErp\Models\Pivots\ProductSupplier;

test('the pivot fields are the editable columns of the pivot table', function (): void {
    expect(resolve_static(ProductSupplier::class, 'pivotFields'))
        ->toEqualCanonicalizing(['contact_id', 'manufacturer_product_number', 'purchase_price']);
});

test('the pivot fields leave out the primary key and the owning key', function (): void {
    expect(resolve_static(ProductSupplier::class, 'pivotFields'))
        ->not->toContain('pivot_id')
        ->not->toContain('product_id');
});
