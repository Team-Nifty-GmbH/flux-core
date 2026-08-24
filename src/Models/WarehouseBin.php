<?php

namespace FluxErp\Models;

use FluxErp\Enums\WarehouseBinTypeEnum;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class WarehouseBin extends FluxModel implements InteractsWithDataTables
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

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return $this->warehouse_bin_type_enum
            ? __(Str::headline($this->warehouse_bin_type_enum->value))
            : null;
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
        return $this->hasMany(StockPosting::class, 'warehouse_bin_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // Scopes
    public function scopeWithBinStock(Builder $query): void
    {
        $query->withSum('stockPostings as stock', 'posting')
            ->withSum('stockPostings as available_stock', 'remaining_stock');
    }
}
