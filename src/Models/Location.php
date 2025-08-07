<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends FluxModel
{
    use HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function countryRegion(): BelongsTo
    {
        return $this->belongsTo(CountryRegion::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}