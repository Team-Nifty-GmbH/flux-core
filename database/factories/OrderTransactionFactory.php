<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Pivots\OrderTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderTransactionFactory extends Factory
{
    protected $model = OrderTransaction::class;

    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 0, 10000),
        ];
    }
}
