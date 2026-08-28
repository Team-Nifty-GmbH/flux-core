<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TeamNiftyGmbH\DataTable\Helpers\SchemaInfo;
use TeamNiftyGmbH\DataTable\ModelInfo\Attribute;

class ProductSupplier extends FluxPivot
{
    protected $table = 'product_supplier';

    // Public static methods
    public static function pivotFields(): array
    {
        $ownerKey = app(static::class)->product()->getForeignKeyName();

        return SchemaInfo::forModel(static::class)
            ->attributes
            ->reject(fn (Attribute $attribute): bool => $attribute->primary
                || $attribute->virtual
                || $attribute->name === $ownerKey
            )
            ->pluck('name')
            ->values()
            ->all();
    }

    // Relations
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
