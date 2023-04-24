<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AddressType extends Model
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes, HasTranslations;

    protected $hidden = [
        'uuid',
    ];

    protected $casts = [
        'uuid' => 'string',
        'is_lock' => 'boolean',
        'is_unique' => 'boolean',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    public array $translatable = [
        'name',
    ];

    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'address_address_type_order')->withPivot('address_id');
    }
}
