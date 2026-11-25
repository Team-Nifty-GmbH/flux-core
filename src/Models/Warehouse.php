<?php

namespace FluxErp\Models;

use FluxErp\Enums\StockRemovalStrategyEnum;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasDefault;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends FluxModel
{
    use Filterable, HasDefault, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, Searchable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'stock_removal_strategy_enum' => StockRemovalStrategyEnum::class,
            'is_default' => 'boolean',
            'requires_storage_area' => 'boolean',
        ];
    }

    // Relations
    public function stockPostings(): HasMany
    {
        return $this->hasMany(StockPosting::class, 'warehouse_id');
    }

    public function storageAreas(): HasMany
    {
        return $this->hasMany(StorageArea::class, 'warehouse_id');
    }
}
