<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecordOrigin extends FluxModel
{
    use HasPackageFactory, HasUserModification, LogsActivity;

    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relations
    /**
     * @return HasMany<Contact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'record_origin_id');
    }

    /**
     * @return HasMany<Lead, $this>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'record_origin_id');
    }
}
