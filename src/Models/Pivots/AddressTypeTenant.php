<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\AddressType;
use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressTypeTenant extends FluxPivot
{
    protected $table = 'address_type_tenant';

    // Relations
    public function addressType(): BelongsTo
    {
        return $this->belongsTo(AddressType::class, 'address_type_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
