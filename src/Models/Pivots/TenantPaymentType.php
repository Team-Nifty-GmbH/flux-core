<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\PaymentType;
use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantPaymentType extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    protected $table = 'tenant_payment_type';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(TenantPaymentType::class, 'payment_type_id', 'payment_type_id');
    }
}
