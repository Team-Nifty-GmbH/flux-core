<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Widget extends FluxModel
{
    use HasPackageFactory, HasUuid;

    protected $guarded = [
        'id',
    ];

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }
}
