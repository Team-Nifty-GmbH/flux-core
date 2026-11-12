<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductProductConfigurationGroup extends FluxPivot
{
    protected $table = 'product_product_configuration_group';

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productConfigurationGroup(): BelongsTo
    {
        return $this->belongsTo(ProductConfigurationGroup::class, 'product_configuration_group_id');
    }
}
