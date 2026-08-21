<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\WarehouseBinTypeEnum;
use FluxErp\Models\WarehouseBin;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseBinFactory extends Factory
{
    protected $model = WarehouseBin::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('??-##-##'),
            'name' => fake()->word(),
            'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
            'is_storage_location' => true,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
