<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\SepaMandate;
use Illuminate\Database\Eloquent\Factories\Factory;

class SepaMandateFactory extends Factory
{
    protected $model = SepaMandate::class;

    public function definition(): array
    {
        return [
            'signed_date' => $this->faker->boolean ? $this->faker->date : null,
        ];
    }
}
