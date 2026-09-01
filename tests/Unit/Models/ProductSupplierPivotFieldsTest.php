<?php

use FluxErp\Models\Contact;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\Product\SupplierRuleset;

test('the relation declares the editable pivot columns', function (): void {
    expect(app(Product::class)->suppliers()->getPivotColumns())
        ->toEqualCanonicalizing(['manufacturer_product_number', 'purchase_price']);
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
