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
            'name' => fake()->sentence(3),
            'to' => $this->generateEmailArray(),
            'cc' => fake()->boolean(30) ? $this->generateEmailArray() : null,
            'bcc' => fake()->boolean(10) ? $this->generateEmailArray() : null,
            'subject' => fake()->sentence(),
            'html_body' => '<p>' . fake()->paragraph() . '</p><p>' . fake()->paragraph() . '</p>',
            'text_body' => fake()->paragraph() . "\n\n" . fake()->paragraph(),
        ];
    }

    private function generateEmailArray(): array
    {
        $count = fake()->numberBetween(1, 4);
        $emails = [];

        for ($i = 0; $i < $count; $i++) {
            $emails[] = fake()->email();
        }

        return $emails;
    }
}
