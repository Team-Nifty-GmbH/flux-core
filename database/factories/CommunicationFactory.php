<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Communication;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunicationFactory extends Factory
{
    protected $model = Communication::class;

    public function definition(): array
    {
        $fromMap = [
            'letter' => $this->faker->address(),
            'mail' => $this->faker->email(),
            'phone-call' => $this->faker->phoneNumber(),
        ];

        $toMap = [
            'letter' => $this->faker->address(),
            'mail' => $this->faker->email(),
            'phone-call' => $this->faker->phoneNumber(),
        ];

        $cc = [
            'letter' => null,
            'mail' => $this->faker->email(),
            'phone-call' => null,
        ];

        $bcc = [
            'letter' => null,
            'mail' => $this->faker->email(),
            'phone-call' => null,
        ];

        $totalTimeMs = [
            'letter' => $this->faker->numberBetween(86400000, 604800000), // 1d to 7d
            'mail' => $this->faker->numberBetween(3600000, 259200000), // 1h to 3d
            'phone-call' => $this->faker->numberBetween(30000, 7200000), // 30s to 2h
        ];

        $isSeen = [
            'letter' => $this->faker->boolean(),
            'mail' => $this->faker->boolean(),
            'phone-call' => true,
        ];

        $startTime = $this->faker->dateTimeBetween('-5 year');

        return [
            'uuid' => $this->faker->uuid(),

            'from' => function (array $attrs) use ($fromMap) {
                return $fromMap[$attrs['communication_type_enum']] ?? null;
            },

            'to' => function (array $attrs) use ($toMap) {
                return $toMap[$attrs['communication_type_enum']] ?? null;
            },

            'cc' => function (array $attrs) use ($cc) {
                return $cc[$attrs['communication_type_enum']] ?? null;
            },

            'bcc' => function (array $attrs) use ($bcc) {
                return $bcc[$attrs['communication_type_enum']] ?? null;
            },

            'started_at' => $startTime,

            'total_time_ms' => function (array $attrs) use ($totalTimeMs) {
                return $totalTimeMs[$attrs['communication_type_enum']] ?? null;
            },

            'subject' => $this->faker->sentence(),

            'text_body' => $this->faker->realText(),

            'is_seen' => function (array $attrs) use ($isSeen) {
                return $isSeen[$attrs['communication_type_enum']] ?? null;
            },
        ];
    }
}
