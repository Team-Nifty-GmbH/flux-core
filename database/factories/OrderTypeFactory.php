<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderTypeFactory extends Factory
{
    protected $model = OrderType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(90),
            'is_hidden' => $this->faker->boolean(10),
            'order_type_enum' => $this->faker->randomElement(OrderTypeEnum::cases())->value,
        ];
    }
}
