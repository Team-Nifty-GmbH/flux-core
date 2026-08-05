<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanInstallmentTransactionFactory extends Factory
{
    protected $model = LoanInstallmentTransaction::class;

    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, -10000, 0),
            'is_accepted' => fake()->boolean(),
        ];
    }
}
