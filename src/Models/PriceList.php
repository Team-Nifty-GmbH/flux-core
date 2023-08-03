<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PriceList extends Model
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $hidden = [
        'uuid',
    ];

    protected $casts = [
        'is_net' => 'boolean',
        'is_default' => 'boolean'
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    public function discount(): MorphOne
    {
        return $this->morphOne(Discount::class, 'model');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PriceList::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PriceList::class, 'parent_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }
}
