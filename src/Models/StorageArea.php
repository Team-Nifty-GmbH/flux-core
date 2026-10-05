<?php

namespace FluxErp\Models;

use FluxErp\Enums\StorageAreaTypeEnum;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Model\SortableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;

class StorageArea extends FluxModel implements Sortable
{
    use Filterable, HasPackageFactory, HasParentChildRelations, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes, SortableTrait;

    public array $sortable = [
        'order_column_name' => 'sort_number',
        'sort_when_creating' => true,
    ];

    protected function casts(): array
    {
        return [
            'storage_area_type_enum' => StorageAreaTypeEnum::class,
            'is_active' => 'boolean',
            'is_storage_location' => 'boolean',
        ];
    }

    // Relations
    public function stockPostings(): HasMany
    {
        return $this->hasMany(StockPosting::class, 'storage_area_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // Scopes
    protected function scopeWithStock(Builder $query): void
    {
        $query->withSum('stockPostings as stock', 'posting')
            ->withSum('stockPostings as available_stock', 'remaining_stock');
    }
}
