<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\DocumentGenerationSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @deprecated
 */
class DocumentGenerationSettingFactory extends Factory
{
    protected $model = DocumentGenerationSetting::class;

    public function definition(): array
    {
        return [
            'is_active' => $this->faker->boolean(30),
            'is_generation_preset' => $this->faker->boolean(50),
            'is_generation_forced' => $this->faker->boolean(30),
            'is_print_preset' => $this->faker->boolean(20),
            'is_print_forced' => $this->faker->boolean(10),
            'is_email_preset' => $this->faker->boolean(20),
            'is_email_forced' => $this->faker->boolean(10),
        ];
    }
}
