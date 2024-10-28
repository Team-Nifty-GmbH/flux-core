<?php

namespace FluxErp\Actions\Schedule;

use FluxErp\Actions\FluxAction;
use FluxErp\Facades\Repeatable;
use FluxErp\Models\Schedule;
use FluxErp\Rulesets\Schedule\UpdateScheduleRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateSchedule extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateScheduleRuleset::class;
    }

    public static function models(): array
    {
        return [Schedule::class];
    }

    public function performAction(): Model
    {
        $schedule = resolve_static(Schedule::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if ($this->data['parameters'] ?? []) {
            $repeatable = Repeatable::get($schedule->name);

            // Remove empty class parameters
            $this->data['parameters'] = array_merge($repeatable['parameters'], $this->data['parameters'] ?? []);
            $this->data['parameters'] = array_filter($this->data['parameters']);
        }

        // Reset recurrences and current_recurrence to null if switched from recurrences to ends_at
        if (($this->data['ends_at'] ?? false) && $schedule->recurrences) {
            $schedule->recurrences = null;
            $schedule->current_recurrence = null;
        }

        // Reset ends_at to null if switched from ends_at to recurrences
        if (($this->data['recurrences'] ?? false) && $schedule->ends_at) {
            $schedule->ends_at = null;
        }

        $schedule->fill($this->data);
        $schedule->save();

        return $schedule->withoutRelations()->fresh();
    }
}
