<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\InstallmentIntervalEnum;
use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'number' => fake()->bothify('L-####'),
            'amount' => fake()->randomFloat(2, 10000, 500000),
            'interest_rate' => fake()->randomFloat(4, 0, 0.1),
            'repayment_type_enum' => fake()->randomElement(RepaymentTypeEnum::cases()),
            'number_of_installments' => fake()->numberBetween(12, 120),
            'installment_interval_enum' => InstallmentIntervalEnum::Monthly,
            'grace_period_installments' => 0,
            'allows_extra_repayments' => true,
            'installment_amount' => fake()->randomFloat(2, 100, 5000),
            'starts_at' => fake()->date(),
            'ends_at' => fake()->dateTimeBetween('+1 year', '+10 years')->format('Y-m-d'),
        ];
    }
}
