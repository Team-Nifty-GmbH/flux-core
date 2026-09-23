<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\ScheduleAdjustmentTypeEnum;
use FluxErp\Models\LoanExtraRepayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanExtraRepaymentFactory extends Factory
{
    protected $model = LoanExtraRepayment::class;

    public function definition(): array
    {
        return [
            'executed_at' => fake()->date(),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'schedule_adjustment_type_enum' => fake()->randomElement(ScheduleAdjustmentTypeEnum::cases()),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
