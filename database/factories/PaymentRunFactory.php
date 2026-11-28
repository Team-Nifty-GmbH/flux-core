<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Models\PaymentRun;
use FluxErp\States\PaymentRun\Open;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentRunFactory extends Factory
{
    protected $model = PaymentRun::class;

    public function definition(): array
    {
        return [
            'state' => Open::$name,
            'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
            'instructed_execution_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'is_instant_payment' => $this->faker->boolean(),
            'is_single_booking' => $this->faker->boolean(),
        ];
    }
}
