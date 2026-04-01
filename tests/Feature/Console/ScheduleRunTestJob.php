<?php

namespace FluxErp\Tests\Feature\Console;

use FluxErp\Traits\Job\TracksSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class ScheduleRunTestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TracksSchedule;

    public function handle(): void
    {
        throw new RuntimeException('Job failed');
    }
}
