<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PriceList;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PriceListFactory extends Factory
{
    protected $model = PriceList::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle(),
            'price_list_code' => Str::uuid()->toString(),
            'is_net' => $this->faker->boolean(),
        ];
    }
}
