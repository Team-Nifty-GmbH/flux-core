<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\OrderType;
use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTypeTenant extends FluxPivot
{
    protected $table = 'order_type_tenant';

    // Relations
    /**
     * @return BelongsTo<OrderType, $this>
     */
    public function orderType(): BelongsTo
    {
        return $this->belongsTo(OrderType::class, 'order_type_id');
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
