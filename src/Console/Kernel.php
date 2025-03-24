<?php

namespace FluxErp\Console;

use FluxErp\Jobs\ArtisanJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Laravel\Sanctum\Console\Commands\PruneExpired;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new ArtisanJob(PruneCommand::class))
            ->name('model:prune')
            ->daily();

        $schedule->job(new ArtisanJob(PruneExpired::class))
            ->name('sanctum:prune-expired')
            ->daily();
    }
}
