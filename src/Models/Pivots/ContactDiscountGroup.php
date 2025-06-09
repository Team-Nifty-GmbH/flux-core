<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\DiscountGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactDiscountGroup extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $table = 'contact_discount_group';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function discountGroup(): BelongsTo
    {
        return $this->belongsTo(DiscountGroup::class);
    }
}
