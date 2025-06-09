<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Models\PaymentRun;
use FluxErp\States\PaymentRun\PaymentRunState;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentRunFactory extends Factory
{
    protected $model = PaymentRun::class;

    public function definition(): array
    {
        return [
            'state' => $this->faker->randomElement(PaymentRunState::all()->keys()),
            'payment_run_type_enum' => $this->faker->randomElement(PaymentRunTypeEnum::values()),
            'instructed_execution_date' => $this->faker->date('Y-m-d'),
            'is_single_booking' => $this->faker->boolean(75),
            'is_instant_payment' => $this->faker->boolean(),
        ];
    }
}
