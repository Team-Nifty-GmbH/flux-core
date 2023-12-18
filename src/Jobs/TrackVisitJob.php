<?php

namespace FluxErp\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrackVisitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $url, public array $properties)
    {
        //
    }

    public function handle(): void
    {
        activity()
            ->withProperties($this->properties)
            ->event('visit')
            ->log($this->url);
    }
}
