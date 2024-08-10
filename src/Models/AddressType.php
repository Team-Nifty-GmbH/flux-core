<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AddressType extends Model
{
    use CacheModelQueries, HasClientAssignment, HasPackageFactory,  HasUserModification, HasUuid,
        LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'is_unique' => 'boolean',
        ];
    }

    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'address_address_type_order')->withPivot('address_id');
    }
}
