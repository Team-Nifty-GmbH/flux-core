<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\TicketState;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'ticket_number' => fake()->uuid(),
            'title' => fake()->text,
            'description' => fake()->realText,
            'state' => fake()->randomElement(TicketState::getStateMapping()->keys()->toArray()),
        ];
    }
}
