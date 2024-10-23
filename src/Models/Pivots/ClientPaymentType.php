<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class ClientPaymentType extends FluxPivot
{
    use BroadcastsEvents;

    protected $table = 'client_payment_type';

    public $timestamps = false;

    public $incrementing = true;

    protected $primaryKey = 'pivot_id';

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(ClientPaymentType::class, 'payment_type_id', 'payment_type_id');
    }
}
