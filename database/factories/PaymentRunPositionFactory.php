<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentRunPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentRunPositionFactory extends Factory
{
    protected $model = PaymentRunPosition::class;

    public function definition(): array
    {
        return [
            'payment_run_id' => PaymentRun::factory(),
            'iban' => $this->faker->iban('DE'),
            'bic' => $this->faker->swiftBicNumber(),
            'account_holder' => $this->faker->company(),
            'amount' => $this->faker->randomFloat(2, -10000, -1),
            'purpose' => $this->faker->word(),
        ];
    }
}
