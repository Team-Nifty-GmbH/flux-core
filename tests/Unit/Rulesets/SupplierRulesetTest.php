<?php

use FluxErp\Rulesets\Product\SupplierRuleset;

test('pivot fields are derived from the rules', function (): void {
    expect(resolve_static(SupplierRuleset::class, 'pivotFields'))
        ->toEqualCanonicalizing(['contact_id', 'manufacturer_product_number', 'purchase_price']);
});

test('pivot fields include the fields a customized ruleset adds', function (): void {
    $ruleset = new class() extends SupplierRuleset
    {
        public function rules(): array
        {
            return array_merge(
                parent::rules(),
                ['suppliers.*.delivery_time_days' => 'integer|nullable']
            );
        }
    };

    $this->app->bind(SupplierRuleset::class, get_class($ruleset));

    expect(resolve_static(SupplierRuleset::class, 'pivotFields'))
        ->toContain('delivery_time_days');
});
