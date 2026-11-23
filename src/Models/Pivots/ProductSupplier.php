<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\Product;
use FluxErp\Models\Unit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSupplier extends FluxPivot
{
    protected static array $formFields = [
        'supplier_product_number' => ['label' => 'Supplier product number'],
        'supplier_product_name' => ['label' => 'Supplier product name'],
        'manufacturer_product_number' => ['label' => 'Manufacturer product number'],
        'packaging_amount' => ['label' => 'Amount per Packaging', 'type' => 'number', 'step' => '0.01'],
        'packaging_unit_id' => ['label' => 'Packaging Unit', 'type' => 'unit'],
        'items_per_packaging' => ['label' => 'Items per Packaging', 'type' => 'number', 'step' => '1'],
        'packaging_ean' => ['label' => 'Packaging EAN'],
        'purchase_price' => ['label' => 'Purchase Price', 'type' => 'number', 'step' => '0.01'],
        'note' => ['label' => 'Note'],
    ];

    protected $table = 'product_supplier';

    public static function formFields(): array
    {
        return static::$formFields;
    }

    public static function pivotColumns(): array
    {
        return array_values(array_unique(array_merge(parent::pivotColumns(), array_keys(static::formFields()))));
    }

    // Relations
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function packagingUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
