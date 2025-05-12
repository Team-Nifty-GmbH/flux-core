<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressAddressType extends FluxPivot
{
    use HasPackageFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = ['address_id', 'address_type_id'];

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
