<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\QueueMonitor;
use FluxErp\States\QueueMonitor\Queued;
use Illuminate\Database\Eloquent\Factories\Factory;

class QueueMonitorFactory extends Factory
{
    protected $model = QueueMonitor::class;

    public function definition(): array
    {
        return [
            'job_id' => fake()->uuid(),
            'job_uuid' => fake()->uuid(),
            'name' => fake()->word(),
            'queue' => 'default',
            'state' => Queued::class,
            'queued_at' => now(),
            'started_at' => now(),
            'attempt' => 1,
            'progress' => 0,
            'retried' => false,
        ];
    }
}
