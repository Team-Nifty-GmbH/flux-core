<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactOption extends Model
{
    use HasPackageFactory, HasUserModification;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(static::class, 'address_id', 'address_id')
            ->where('type', $this->type)
            ->where('id', '!=', $this->id);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
