<?php

use FluxErp\Models\Contact;
use FluxErp\Models\Pivots\ProductSupplier;
use FluxErp\Models\Product;
use FluxErp\Models\Unit;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\Product\SupplierRuleset;
use Illuminate\Support\Facades\Validator;

test('the pivot fields are the editable columns of the pivot table', function (): void {
    expect(resolve_static(ProductSupplier::class, 'pivotFields'))
        ->toEqualCanonicalizing([
            'contact_id',
            'packaging_unit_id',
            'manufacturer_product_number',
            'supplier_product_number',
            'supplier_product_name',
            'packaging_amount',
            'purchase_price',
            'note',
        ]);
});

test('the pivot fields leave out the primary key and the owning key', function (): void {
    expect(resolve_static(ProductSupplier::class, 'pivotFields'))
        ->not->toContain('pivot_id')
        ->not->toContain('product_id');
});

test('every pivot column is accepted by the supplier rules', function (): void {
    $rules = array_keys(resolve_static(SupplierRuleset::class, 'getRules'));

    foreach (resolve_static(ProductSupplier::class, 'pivotFields') as $field) {
        expect($rules)->toContain('suppliers.*.' . $field);
    }
});

test('the purchase data of a supplier survives a save', function (): void {
    $product = Product::factory()->create(['vat_rate_id' => VatRate::factory()->create()->getKey()]);
    $product->tenants()->attach($this->dbTenant->getKey());

    $contact = Contact::factory()->create();
    $unit = Unit::factory()->create(['name' => 'Karton']);

    $product->suppliers()->attach($contact->getKey(), [
        'supplier_product_number' => 'L-4711',
        'supplier_product_name' => 'Schrauben 4x30 Grosspackung',
        'packaging_amount' => 250,
        'packaging_unit_id' => $unit->getKey(),
        'purchase_price' => 12.5,
        'note' => 'Nur in ganzen Kartons lieferbar.',
    ]);

    $pivot = resolve_static(ProductSupplier::class, 'query')
        ->where('product_id', $product->getKey())
        ->where('contact_id', $contact->getKey())
        ->firstOrFail();

    expect($pivot->supplier_product_number)->toBe('L-4711')
        ->and($pivot->supplier_product_name)->toBe('Schrauben 4x30 Grosspackung')
        ->and($pivot->packaging_amount)->toEqual(250)
        ->and($pivot->packagingUnit->name)->toBe('Karton')
        ->and($pivot->note)->toBe('Nur in ganzen Kartons lieferbar.');
});

test('a packaging amount and its unit are only valid together', function (): void {
    $rules = resolve_static(SupplierRuleset::class, 'getRules');
    $supplier = [
        'contact_id' => Contact::factory()->create()->getKey(),
        'packaging_amount' => 250,
        'packaging_unit_id' => Unit::factory()->create()->getKey(),
    ];

    $errorsFor = fn (array $attributes): array => Validator::make(
        ['suppliers' => [array_merge($supplier, $attributes)]],
        $rules
    )
        ->errors()
        ->keys();

    expect($errorsFor(['packaging_unit_id' => null]))->toContain('suppliers.0.packaging_unit_id')
        ->and($errorsFor(['packaging_amount' => null]))->toContain('suppliers.0.packaging_amount')
        ->and($errorsFor([]))->toBeEmpty()
        ->and($errorsFor(['packaging_amount' => null, 'packaging_unit_id' => null]))->toBeEmpty();
});
