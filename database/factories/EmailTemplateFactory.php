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
            'to' => $this->generateEmailArray(),
            'cc' => $this->faker->boolean(30) ? $this->generateEmailArray() : null,
            'bcc' => $this->faker->boolean(10) ? $this->generateEmailArray() : null,
            'subject' => $this->faker->sentence(),
            'html_body' => '<p>' . $this->faker->paragraph() . '</p><p>' . $this->faker->paragraph() . '</p>',
            'text_body' => $this->faker->paragraph() . "\n\n" . $this->faker->paragraph(),
        ];
    }

    private function generateEmailArray(): array
    {
        $count = $this->faker->numberBetween(1, 4);
        $emails = [];

        for ($i = 0; $i < $count; $i++) {
            $emails[] = $this->faker->email();
        }

        return $emails;
    }
}
