<?php

namespace FluxErp\Actions\Schedule;

use FluxErp\Actions\FluxAction;
use FluxErp\Facades\Repeatable;
use FluxErp\Http\Requests\UpdateScheduleRequest;
use FluxErp\Models\Schedule;
use Illuminate\Database\Eloquent\Model;

class UpdateSchedule extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateScheduleRequest())->rules();
    }

    public static function models(): array
    {
        return [Schedule::class];
    }

    public function performAction(): Model
    {
        $schedule = Schedule::query()
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
