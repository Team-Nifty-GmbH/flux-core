<?php

use FluxErp\Models\Contact;
use FluxErp\Models\Pivots\ProductSupplier;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\Product\SupplierRuleset;

test('the pivot model declares the editable columns', function (): void {
    expect(resolve_static(ProductSupplier::class, 'pivotColumns'))
        ->toEqualCanonicalizing(['manufacturer_product_number', 'purchase_price']);
});

test('the relation carries what the pivot model declares', function (): void {
    expect(app(Product::class)->suppliers()->getPivotColumns())
        ->toEqualCanonicalizing(resolve_static(ProductSupplier::class, 'pivotColumns'));
});

test('every declared pivot column is accepted by the supplier rules', function (): void {
    $rules = array_keys(resolve_static(SupplierRuleset::class, 'getRules'));

    foreach (app(Product::class)->suppliers()->getPivotColumns() as $field) {
        expect($rules)->toContain('suppliers.*.' . $field);
    }
});

test('the declared pivot columns come back through the relation', function (): void {
    $product = Product::factory()->create(['vat_rate_id' => VatRate::factory()->create()->getKey()]);
    $contact = Contact::factory()->create();

    $product->suppliers()->attach($contact->getKey(), [
        'manufacturer_product_number' => 'MPN-4711',
        'purchase_price' => 12.5,
    ]);

    $pivot = $product->load('suppliers')->suppliers->first()->pivot;

    expect($pivot->manufacturer_product_number)->toBe('MPN-4711')
        ->and($pivot->purchase_price)->toEqual(12.5);
});
