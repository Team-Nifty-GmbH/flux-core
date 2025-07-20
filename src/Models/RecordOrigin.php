<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecordOrigin extends FluxModel
{
    use CacheModelQueries, HasPackageFactory, HasUserModification, LogsActivity;

    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'record_origin_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'record_origin_id');
    }
}
