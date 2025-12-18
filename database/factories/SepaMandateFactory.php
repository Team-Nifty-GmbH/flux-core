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
            'sepa_mandate_type_enum' => fake()->boolean() ? SepaMandateTypeEnum::BASIC : SepaMandateTypeEnum::B2B,
            'signed_date' => fake()->boolean ? fake()->date : null,
        ];
    }
}
