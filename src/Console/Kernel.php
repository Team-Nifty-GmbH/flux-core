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
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new ArtisanJob(PruneCommand::class))
            ->name('model:prune')
            ->daily();

        $schedule->job(new ArtisanJob(PruneExpired::class))
            ->name('sanctum:prune-expired')
            ->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
