<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\Discount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactDiscount extends FluxPivot
{
    protected $table = 'contact_discount';

    // Relations
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
