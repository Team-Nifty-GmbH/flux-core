<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\DiscountGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactDiscountGroup extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $table = 'contact_discount_group';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function discountGroup(): BelongsTo
    {
        return $this->belongsTo(DiscountGroup::class, 'discount_group_id');
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(ContactDiscountGroup::class, 'discount_group_id', 'discount_group_id');
    }
}
