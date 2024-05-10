<?php

namespace FluxErp\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;

class ArtisanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    private string $command;

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function handle(): void
    {
        Artisan::call($this->command);
    }
}
