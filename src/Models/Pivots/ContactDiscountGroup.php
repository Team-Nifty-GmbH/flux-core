<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\DiscountGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactDiscountGroup extends FluxPivot
{
    protected $table = 'contact_discount_group';

    // Relations
    /**
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return BelongsTo<DiscountGroup, $this>
     */
    public function discountGroup(): BelongsTo
    {
        return $this->belongsTo(DiscountGroup::class);
    }
}
