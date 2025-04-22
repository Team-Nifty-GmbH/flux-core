<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPaymentType extends FluxPivot
{
    use HasPackageFactory;

    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    protected $table = 'client_payment_type';

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(ClientPaymentType::class, 'payment_type_id', 'payment_type_id');
    }
}
