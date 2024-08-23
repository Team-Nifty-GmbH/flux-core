<?php

namespace FluxErp\Models;

use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryRegion extends Model
{
    use Commentable, Filterable, HasPackageFactory, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
