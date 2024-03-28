<?php

namespace FluxErp\Models;

use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\States\PaymentRun\PaymentRunState;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentRun extends Model
{
    use HasFrontendAttributes, HasUserModification, HasUuid;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'state' => PaymentRunState::class,
            'payment_run_type_enum' => PaymentRunTypeEnum::class,
            'instructed_execution_date' => 'date',
            'is_instant_payment' => 'boolean',
        ];
    }

    public function bankConnection(): BelongsTo
    {
        return $this->belongsTo(BankConnection::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_payment_run');
    }
}
