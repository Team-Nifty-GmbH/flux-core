<?php

namespace FluxErp\Models;

use FluxErp\Enums\WarehouseBinTypeEnum;
use FluxErp\Models\Pivots\ProductWarehouseBin;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WarehouseBin extends FluxModel
{
    use Filterable, HasPackageFactory, HasParentChildRelations, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes;

    protected function casts(): array
    {
        return [
            'warehouse_bin_type_enum' => WarehouseBinTypeEnum::class,
            'is_storage_location' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_warehouse_bin')
            ->using(ProductWarehouseBin::class)
            ->withPivot(['is_fixed_location', 'min_stock', 'max_stock', 'sort_order']);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
