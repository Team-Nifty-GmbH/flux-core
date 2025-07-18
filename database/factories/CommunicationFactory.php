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
        $date = $this->faker->dateTimeBetween('-1 year');
        $startedAt = $this->faker->boolean(40)
            ? $this->faker->dateTimeBetween($date)
            : null;
        $endedAt = $startedAt && $this->faker->boolean(70)
            ? $this->faker->dateTimeBetween($startedAt)
            : null;

        return [
            'communication_type_enum' => $this->faker->randomElement(CommunicationTypeEnum::cases()),
            'from' => $this->faker->email(),
            'to' => $this->generateEmailArray(),
            'cc' => $this->faker->boolean(30) ? $this->generateEmailArray() : null,
            'bcc' => $this->faker->boolean(10) ? $this->generateEmailArray() : null,
            'subject' => $this->faker->sentence(),
            'text_body' => $this->faker->paragraphs(3, true),
            'html_body' => $this->faker->boolean(70)
                ? '<p>' . $this->faker->paragraphs(3, true) . '</p>'
                : null,
            'is_seen' => $this->faker->boolean(60),
            'date' => $date,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'total_time_ms' => $this->faker->numberBetween(0, 3600000),
            'message_id' => $this->faker->boolean() ? $this->faker->uuid() : null,
            'message_uid' => $this->faker->boolean() ? $this->faker->numberBetween(1, 99999) : null,
        ];
    }

    private function generateEmailArray(): array
    {
        $count = $this->faker->numberBetween(1, 4);
        $emails = [];

        for ($i = 0; $i < $count; $i++) {
            $emails[] = [
                'email' => $this->faker->email(),
                'name' => $this->faker->name(),
            ];
        }

        return $emails;
    }
}
