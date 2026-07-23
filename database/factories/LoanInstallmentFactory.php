<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\LoanInstallment;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanInstallmentFactory extends Factory
{
    protected $model = LoanInstallment::class;

    public function definition(): array
    {
        return [
            'sequence' => fake()->numberBetween(1, 120),
            'due_date' => fake()->date(),
            'principal_amount' => fake()->randomFloat(2, 100, 5000),
            'interest_amount' => fake()->randomFloat(2, 0, 500),
            'is_paid' => fake()->boolean(),
        ];
    }
}
