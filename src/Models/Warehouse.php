<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Warehouse extends Model
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, Searchable, SoftDeletes;

    protected $hidden = [
        'uuid',
    ];

    protected $casts = [
        'uuid' => 'string',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(StockPosting::class, 'warehouse_id');
    }
}
