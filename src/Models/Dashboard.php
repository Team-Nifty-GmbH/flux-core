<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\EloquentSortable\Sortable;

class Dashboard extends Model implements Sortable
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

    public function buildSortQuery()
    {
        return static::query()
            ->where('authenticatable_id', $this->authenticatable_id)
            ->where('authenticatable_type', $this->authenticatable_type);
    }
}
