<?php

namespace FluxErp\Actions\Schedule;

use FluxErp\Actions\FluxAction;
use FluxErp\Facades\Repeatable;
use FluxErp\Models\Schedule;
use FluxErp\Rulesets\Schedule\CreateScheduleRuleset;

class CreateSchedule extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateScheduleRuleset::class, 'getRules');
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

        $schedule = app(Schedule::class, ['attributes' => $this->data]);
        $schedule->save();

        return $schedule->fresh();
    }
}
