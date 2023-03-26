<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ProjectCategoryTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectCategoryTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProjectCategoryTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->jobTitle(),
        ];
    }
}
