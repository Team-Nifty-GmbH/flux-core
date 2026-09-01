<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Address;
use FluxErp\Models\SerialNumber;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressSerialNumber extends FluxPivot
{
    protected $table = 'address_serial_number';

    // Relations
    /**
     * @return BelongsTo<Address, $this>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * @return BelongsTo<SerialNumber, $this>
     */
    public function serialNumber(): BelongsTo
    {
        return $this->belongsTo(SerialNumber::class);
    }
}
