<?php

namespace FluxErp\Models;

use FluxErp\Enums\PaymentIntervalEnum;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryComponent extends FluxModel
{
    use HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'payment_interval' => PaymentIntervalEnum::class,
            'valid_from' => 'date:Y-m-d',
            'valid_until' => 'date:Y-m-d',
        ];
    }

    // Relations
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Public methods
    public function getMonthlyAmount(): string
    {
        return bcdiv(bcmul($this->amount, $this->payment_interval->paymentsPerYear()), 12);
    }
}
