<?php

namespace FluxErp\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class InstallProcessOutputEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        private readonly ?string $batchId = null,
        public ?int $progress = null,
        public ?string $title = null,
        public ?array $message = null)
    {
        //
    }

    public function broadcastOn(): array
    {
        if (! $this->batchId) {
            return [];
        }

        return [
            new Channel('job-batch.'.$this->batchId),
        ];
    }
}
