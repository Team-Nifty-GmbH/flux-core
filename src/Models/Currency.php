<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasDefault;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use CacheModelQueries, Commentable, Filterable, HasDefault, HasPackageFactory, HasUserModification, HasUuid,
        SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'symbol' => 'string',
            'is_default' => 'boolean',
        ];
    }

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }
}
