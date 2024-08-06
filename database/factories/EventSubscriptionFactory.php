<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\EventSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventSubscriptionFactory extends Factory
{
    protected $model = EventSubscription::class;

    public function definition(): array
    {
        return [
            'is_broadcast' => $this->faker->boolean,
            'is_notifiable' => $this->faker->boolean,
        ];
    }
}
