<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PurchaseInvoicePosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseInvoicePositionFactory extends Factory
{
    protected $model = PurchaseInvoicePosition::class;

    public function definition(): array
    {
        $amount = fake()->boolean() ?
            fake()->numberBetween(100, 11000) / 100 :
            fake()->numberBetween(1, 500);
        $unitPrice = fake()->numberBetween(100, 10000) / 100;

        return [
            'name' => fake()->jobTitle(),
            'amount' => $amount,
            'unit_price' => $unitPrice,
            'total_price' => bcmul($amount, $unitPrice),
        ];
    }
}
