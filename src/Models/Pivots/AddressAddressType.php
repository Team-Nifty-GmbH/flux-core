<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressAddressType extends FluxPivot
{
    protected $table = 'address_address_type';

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function addressType(): BelongsTo
    {
        return $this->belongsTo(AddressType::class);
    }
}
