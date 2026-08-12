<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Models\Pivots\OrderPaymentRun;
use FluxErp\States\PaymentRun\PaymentRunState;
use FluxErp\Traits\Model\HasFrontendAttributes;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentRun extends FluxModel
{
    use HasFrontendAttributes, HasUserModification, HasUuid, LogsActivity;

    protected function casts(): array
    {
        return [
            'state' => PaymentRunState::class,
            'payment_run_type_enum' => PaymentRunTypeEnum::class,
            'sepa_mandate_type_enum' => SepaMandateTypeEnum::class,
            'instructed_execution_date' => 'date',
            'is_instant_payment' => 'boolean',
            'total_amount' => Money::class,
        ];
    }

    // Relations
    public function bankConnection(): BelongsTo
    {
        return $this->belongsTo(BankConnection::class);
    }

    public function recalculateTotalAmount(): static
    {
        $this->total_amount = $this->orders()->sum('order_payment_run.amount');

        $this->saveQuietly();

        return $this;
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_payment_run')
            ->using(OrderPaymentRun::class);
    }
}
