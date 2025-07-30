<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailTemplateFactory extends Factory
{
    protected $model = EmailTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'subject' => $this->faker->sentence(),
            'html_body' => '<p>' . $this->faker->paragraph() . '</p><p>' . $this->faker->paragraph() . '</p>',
            'text_body' => $this->faker->paragraph() . "\n\n" . $this->faker->paragraph(),
        ];
    }
}
