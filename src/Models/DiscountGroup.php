<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\ContactDiscountGroup;
use FluxErp\Models\Pivots\DiscountDiscountGroup;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DiscountGroup extends FluxModel
{
    use HasUserModification, HasUuid, LogsActivity;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_discount_group')
            ->using(ContactDiscountGroup::class);
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_discount_group')
            ->using(DiscountDiscountGroup::class);
    }
}
