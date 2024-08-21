<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PaymentNotice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @deprecated
 */
class PaymentNoticeFactory extends Factory
{
    protected $model = PaymentNotice::class;

    public function definition(): array
    {
        return [
            'payment_notice' => $this->faker->sentence(),
        ];
    }
}
