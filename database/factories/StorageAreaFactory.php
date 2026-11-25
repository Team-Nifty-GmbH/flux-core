<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\StorageAreaTypeEnum;
use FluxErp\Models\StorageArea;
use Illuminate\Database\Eloquent\Factories\Factory;

class StorageAreaFactory extends Factory
{
    protected $model = StorageArea::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('??-##-##'),
            'name' => fake()->word(),
            'storage_area_type_enum' => StorageAreaTypeEnum::Container,
            'is_active' => true,
            'is_storage_location' => true,
        ];
    }
}
