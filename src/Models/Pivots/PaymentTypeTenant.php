<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\PaymentType;
use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTypeTenant extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    protected $table = 'payment_type_tenant';

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(PaymentTypeTenant::class, 'payment_type_id', 'payment_type_id');
    }
}
