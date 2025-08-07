<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\AbsenceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbsenceRequestFactory extends Factory
{
    protected $model = AbsenceRequest::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 week', '+3 months');
        $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 14) . ' days');
        
        return [
            'user_id' => 1,
            'absence_type_id' => 1,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'start_half_day' => $this->faker->randomElement(['full', 'first_half', 'second_half']),
            'end_half_day' => $this->faker->randomElement(['full', 'first_half', 'second_half']),
            'days_requested' => $this->faker->randomFloat(2, 0.5, 14),
            'status' => $this->faker->randomElement(['draft', 'pending', 'approved', 'rejected', 'cancelled']),
            'reason' => $this->faker->optional()->sentence(),
            'substitute_user_id' => null,
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            'is_emergency' => $this->faker->boolean(10),
            'client_id' => 1,
        ];
    }
}