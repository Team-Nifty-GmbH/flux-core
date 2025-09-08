<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Communication;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunicationFactory extends Factory
{
    protected $model = Communication::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 year');
        $startedAt = fake()->boolean(40)
            ? fake()->dateTimeBetween($date)
            : null;
        $endedAt = $startedAt && fake()->boolean(70)
            ? fake()->dateTimeBetween($startedAt)
            : null;

        return [
            'communication_type_enum' => fake()->randomElement(CommunicationTypeEnum::cases()),
            'from' => fake()->email(),
            'to' => $this->generateEmailArray(),
            'cc' => fake()->boolean(30) ? $this->generateEmailArray() : null,
            'bcc' => fake()->boolean(10) ? $this->generateEmailArray() : null,
            'subject' => fake()->sentence(),
            'text_body' => fake()->paragraphs(3, true),
            'html_body' => fake()->boolean(70)
                ? '<p>' . fake()->paragraphs(3, true) . '</p>'
                : null,
            'is_seen' => fake()->boolean(60),
            'date' => $date,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'total_time_ms' => fake()->numberBetween(0, 3600000),
            'message_id' => fake()->boolean() ? fake()->uuid() : null,
            'message_uid' => fake()->boolean() ? fake()->numberBetween(1, 99999) : null,
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
