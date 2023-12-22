<?php

namespace FluxErp\Jobs\InstallWizard;

use FluxErp\Events\InstallProcessOutputEvent;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class CommandJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $command, private readonly array $parameters = [])
    {
    }

    public function handle(): void
    {
        Artisan::call($this->command, $this->parameters);

        InstallProcessOutputEvent::dispatch(
            $this->batch()?->id,
            null,
            $this->command,
            explode(PHP_EOL, Artisan::output())
        );
    }
}
