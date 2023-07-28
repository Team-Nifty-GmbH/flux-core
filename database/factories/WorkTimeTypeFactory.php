<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\WorkTimeType;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkTimeTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkTimeType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'is_billable' => $this->faker->boolean()
        ];
    }
}
