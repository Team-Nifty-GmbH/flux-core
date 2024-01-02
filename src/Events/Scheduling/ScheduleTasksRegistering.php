<?php

namespace FluxErp\Events\Scheduling;

use Illuminate\Console\Scheduling\Schedule;

class ScheduleTasksRegistering
{
    public Schedule $schedule;

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }
}
