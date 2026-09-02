<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\AddressType;
use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressTypeTenant extends FluxPivot
{
    protected $table = 'address_type_tenant';

    // Relations
    /**
     * @return BelongsTo<AddressType, $this>
     */
    public function addressType(): BelongsTo
    {
        return $this->belongsTo(AddressType::class, 'address_type_id');
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
