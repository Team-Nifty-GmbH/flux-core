<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Dashboard extends Model
{
    use HasUuid, SortableTrait;

    protected $guarded = [
        'id',
    ];

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(Widget::class);
    }
}
