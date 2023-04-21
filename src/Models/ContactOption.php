<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactOption extends Model
{
    use HasPackageFactory, HasUserModification;

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    public function siblings(): HasMany
    {
        return $this->hasMany(self::class, 'address_id', 'address_id')
            ->where('type', $this->type)
            ->where('id', '!=', $this->id);
    }
}
