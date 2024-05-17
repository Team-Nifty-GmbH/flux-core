<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\WorkTime\CreateLockedWorkTimeRuleset;
use Illuminate\Support\Carbon;

class CreateLockedWorkTime extends CreateWorkTime
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateLockedWorkTimeRuleset::class, 'getRules');
    }

    public function performAction(): WorkTime
    {
        $workTime = app(WorkTime::class, ['attributes' => $this->data]);

        if (is_null(data_get($this->data, 'is_billable'))) {
            $workTime->is_billable = $workTime->workTimeType?->is_billable ?? false;
        }

       if (is_null($workTime->paused_time_ms)) {
            $workTime->paused_time_ms = 0;
        }

        $workTime->total_time_ms = Carbon::parse($workTime->ended_at)
            ->diffInMilliseconds(Carbon::parse($workTime->started_at)) - $workTime->paused_time_ms;

        $workTime->save();

        return $workTime->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['user_id'] ??= auth()->user()->id;

        if (! is_null($this->data['user_id']) && ! is_null($this->data['started_at'])) {
            // add parent_id in case daily work time exists
            $this->data['parent_id'] = WorkTime::query()->where('user_id', $this->data['user_id'])
                ->where('is_daily_work_time', true)
                ->whereDate('started_at', Carbon::parse($this->data['started_at'])->toDateString())
                ->first()->id ?? null;
        }
    }
}
