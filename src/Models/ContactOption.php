<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ContactOption extends FluxModel
{
    use HasPackageFactory, HasUserModification, LogsActivity;

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function contact(): HasOneThrough
    {
        return $this->hasOneThrough(
            Contact::class,
            Address::class,
            'id',
            'id',
            'address_id',
            'contact_id'
        );
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(static::class, 'address_id', 'address_id')
            ->where('type', $this->type)
            ->where('id', '!=', $this->id);
    }
}
