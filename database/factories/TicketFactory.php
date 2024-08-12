<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'ticket_number' => $this->faker->uuid(),
            'title' => $this->faker->text,
            'description' => $this->faker->realText,
        ];
    }
}
