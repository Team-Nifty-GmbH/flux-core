<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\RuleCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

class RuleConditionFactory extends Factory
{
    protected $model = RuleCondition::class;

    public function definition(): array
    {
        return [
            'type' => 'or_container',
            'value' => null,
            'position' => 0,
        ];
    }
}
