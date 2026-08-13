<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Models\Pivots\OrderPaymentRun;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentRunPosition extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid;

    protected function casts(): array
    {
        return [
            'amount' => Money::class,
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function paymentRun(): BelongsTo
    {
        return $this->belongsTo(PaymentRun::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_payment_run', 'payment_run_position_id')
            ->using(OrderPaymentRun::class)
            ->withPivot(['pivot_id', 'payment_run_id', 'amount', 'success']);
    }
}
