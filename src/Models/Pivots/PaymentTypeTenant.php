<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\PaymentType;
use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTypeTenant extends FluxPivot
{
    protected $table = 'payment_type_tenant';

    // Relations
    /**
     * @return BelongsTo<PaymentType, $this>
     */
    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    /**
     * @return HasMany<PaymentTypeTenant, $this>
     */
    public function siblings(): HasMany
    {
        return $this->hasMany(PaymentTypeTenant::class, 'payment_type_id', 'payment_type_id');
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
