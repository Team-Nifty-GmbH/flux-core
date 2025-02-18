<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\Industry;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactIndustry extends FluxPivot
{
    protected $table = 'contact_industry';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function industries(): HasMany
    {
        return $this->hasMany(Industry::class);
    }
}
