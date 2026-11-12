<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Models\Pivots\OrderPaymentRun;
use FluxErp\States\PaymentRun\PaymentRunState;
use FluxErp\Traits\Model\HasFrontendAttributes;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PaymentRun extends FluxModel
{
    use HasFrontendAttributes, HasPackageFactory, HasUserModification, HasUuid, LogsActivity;

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

    public function settledPositionOrderIds(): array
    {
        return resolve_static(OrderPaymentRun::class, 'query')
            ->whereIn(
                'payment_run_position_id',
                $this->positions()->where('amount', 0)->select('id')
            )
            ->pluck('order_id')
            ->all();
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_payment_run')
            ->using(OrderPaymentRun::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(PaymentRunPosition::class);
    }
}
