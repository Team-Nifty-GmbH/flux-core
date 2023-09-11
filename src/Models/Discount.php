<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Discount extends Model
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $hidden = [
        'pivot',
    ];

    protected $casts = [
        'uuid' => 'string',
        'is_percentage' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_discount');
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
