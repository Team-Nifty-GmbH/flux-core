<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Models\PaymentRun;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentRunFactory extends Factory
{
    protected $model = PaymentRun::class;

    public function definition(): array
    {
        return [
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
        ];
    }
}
