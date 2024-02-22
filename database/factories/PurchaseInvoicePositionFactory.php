<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PurchaseInvoicePosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseInvoicePositionFactory extends Factory
{
    protected $model = PurchaseInvoicePosition::class;

    public function definition(): array
    {
        $amount = $this->faker->boolean() ?
            $this->faker->numberBetween(100, 11000) / 100 :
            $this->faker->numberBetween(1, 500);
        $unitPrice = $this->faker->numberBetween(100, 10000) / 100;

        return [
            'name' => $this->faker->jobTitle(),
            'amount' => $amount,
            'unit_price' => $unitPrice,
            'total_price' => bcmul($amount, $unitPrice),
        ];
    }
}
