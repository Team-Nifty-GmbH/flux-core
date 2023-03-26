<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PaymentNotice;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentNoticeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentNotice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'payment_notice' => $this->faker->sentence(),
        ];
    }
}
