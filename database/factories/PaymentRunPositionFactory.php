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
            'iban' => 'DE89370400440532013000',
            'account_holder' => $this->faker->company(),
            'amount' => '-100.00',
            'purpose' => $this->faker->word(),
        ];
    }
}
