<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasDefault;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends FluxModel
{
    use Filterable, HasDefault, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, Searchable, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function stockPostings(): HasMany
    {
        return $this->hasMany(StockPosting::class, 'warehouse_id');
    }
}
