<?php

namespace FluxErp\Actions\Schedule;

use Cron\CronExpression;
use FluxErp\Actions\FluxAction;
use FluxErp\Facades\Repeatable;
use FluxErp\Http\Requests\CreateScheduleRequest;
use FluxErp\Models\Schedule;

class CreateSchedule extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateScheduleRequest())->rules();
    }

    public static function models(): array
    {
        return [Schedule::class];
    }

    public function performAction(): Schedule
    {
        $repeatable = Repeatable::get($this->data['name']);

        $this->data = array_merge($repeatable, $this->data);

        // Remove empty class parameters
        $this->data['parameters'] = array_filter($this->data['parameters'] ?? []);

        $schedule = new Schedule($this->data);
        $schedule->save();

        return $schedule->fresh();
    }
}
