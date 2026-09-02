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
use Illuminate\Support\Facades\DB;

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
    /**
     * @return BelongsTo<BankConnection, $this>
     */
    public function bankConnection(): BelongsTo
    {
        return $this->belongsTo(BankConnection::class);
    }

    public function recalculateTotalAmount(): static
    {
        static::query()
            ->whereKey($this->getKey())
            ->update([
                'total_amount' => DB::raw(
                    '(select coalesce(sum(`amount`), 0) from `order_payment_run` '
                    . 'where `order_payment_run`.`payment_run_id` = `payment_runs`.`id`)'
                ),
            ]);

        return $this;
    }

    /**
     * @return BelongsToMany<Order, $this>
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_payment_run')
            ->using(OrderPaymentRun::class);
    }
}
