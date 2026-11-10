<?php

namespace FluxErp\Tests\Fixtures;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

class FlareFailingJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $email) {}

    public function handle(): void
    {
        throw new RuntimeException('Job broke');
    }

    public function tags(): array
    {
        return ['contact:' . $this->email];
    }
}
