<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\Discount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactDiscount extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $table = 'contact_discount';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
