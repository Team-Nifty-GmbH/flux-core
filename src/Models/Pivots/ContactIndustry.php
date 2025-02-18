<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\Industry;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactIndustry extends FluxPivot
{
    protected $table = 'contact_industry';

    protected $primaryKey = 'pivot_id';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }
}
