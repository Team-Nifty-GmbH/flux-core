<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\WorkTime\CreateLockedWorkTimeRuleset;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CreateLockedWorkTime extends CreateWorkTime
{
    protected function getRulesets(): string|array
    {
        return CreateLockedWorkTimeRuleset::class;
    }

    public function performAction(): WorkTime
    {
        $workTime = app(WorkTime::class, ['attributes' => $this->data]);

        $workTime->paused_time_ms ??= 0;
        $workTime->is_billable ??= $workTime->workTimeType?->is_billable ?? false;

        if ($workTime->ended_at) {
            $workTime->total_time_ms = bcsub(
                $workTime->ended_at->diffInMilliseconds($workTime->started_at),
                $workTime->paused_time_ms
            );
        } else {
            $workTime->is_locked = false;
        }

        $workTime->save();

        return $workTime->fresh();
    }

    protected function prepareForValidation(): void
    {
        if (data_get($this->data, 'user_id') && data_get($this->data, 'started_at')) {
            // add parent_id in case daily work time exists
            $this->data['parent_id'] = resolve_static(WorkTime::class, 'query')
                ->where('user_id', $this->data['user_id'])
                ->where('started_at', '<=', Carbon::parse($this->data['started_at'])->toDateTimeString())
                ->where(function ($query): void {
                    $query->where('ended_at', '>', Carbon::parse($this->data['started_at'])->toDateTimeString())
                        ->orWhereNull('ended_at');
                })
                ->where('is_daily_work_time', true)
                ->value('id');
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($endedAt = data_get($this->data, 'ended_at')) {
            $totalTimeMs = Carbon::parse($endedAt)->diffInMilliseconds(Carbon::parse($this->data['started_at']))
                - data_get($this->data, 'paused_time_ms', 0);

            if ($totalTimeMs < 0) {
                throw ValidationException::withMessages([
                    'paused_time_ms' => [__('Pause can not be longer than time between started_at and ended_at.')],
                ])->errorBag('createLockedWorkTime');
            }
        }
    }
}
