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
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class StorageArea extends FluxModel implements InteractsWithDataTables, Sortable
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

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        if (! $this->storage_area_type_enum) {
            return null;
        }

        return collect(StorageAreaTypeEnum::valuesLocalized())
            ->firstWhere('value', $this->storage_area_type_enum->value)['label'] ?? null;
    }

    public function getLabel(): ?string
    {
        return $this->name ? $this->code . ' - ' . $this->name : $this->code;
    }

    public function getUrl(): ?string
    {
        return null;
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
