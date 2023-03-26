<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ticket_number' => $this->faker->uuid(),
            'title' => $this->faker->text,
            'description' => $this->faker->realText,
        ];
    }
}
