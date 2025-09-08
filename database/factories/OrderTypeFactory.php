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
            'name' => fake()->firstName(),
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(90),
            'is_hidden' => fake()->boolean(10),
            'order_type_enum' => fake()->randomElement(OrderTypeEnum::cases())->value,
        ];
    }
}
