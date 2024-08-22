<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketTypeFactory extends Factory
{
    protected $model = TicketType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle(),
        ];
    }
}
