<?php

namespace FluxErp\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrackVisitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly ?Model $user,
        private readonly string $url,
        private readonly array $properties
    ) {
        //
    }

    public function handle(): void
    {
        activity()
            ->causedBy($this->user)
            ->withProperties($this->properties)
            ->event('visit')
            ->log($this->url);
    }
}
