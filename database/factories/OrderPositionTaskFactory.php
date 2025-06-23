<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Pivots\OrderPositionTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderPositionTaskFactory extends Factory
{
    protected $model = OrderPositionTask::class;

    public function definition(): array
    {
        return [
            'amount' => $this->faker->numberBetween(10, 100),
        ];
    }
}
