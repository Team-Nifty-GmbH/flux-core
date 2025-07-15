<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Models\SepaMandate;
use Illuminate\Database\Eloquent\Factories\Factory;

class SepaMandateFactory extends Factory
{
    protected $model = SepaMandate::class;

    public function definition(): array
    {
        return [
            'sepa_mandate_type_enum' => rand(0, 1) ? SepaMandateTypeEnum::BASIC : SepaMandateTypeEnum::B2B,
            'signed_date' => $this->faker->boolean ? $this->faker->date : null,
        ];
    }
}
