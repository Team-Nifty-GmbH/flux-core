<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Pivots\OrderPositionStockPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderPositionStockPostingFactory extends Factory
{
    protected $model = OrderPositionStockPosting::class;

    public function definition(): array
    {
        return [
            'reserved_amount' => $this->faker->numberBetween(1, 1000),
        ];
    }
}
