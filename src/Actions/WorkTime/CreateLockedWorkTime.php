<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\WorkTime\CreateLockedWorkTimeRuleset;
use Illuminate\Database\Eloquent\Builder;
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
        if (data_get($this->data, 'user_id')
            && data_get($this->data, 'started_at')
            && ! data_get($this->data, 'is_daily_work_time')
        ) {
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

        if ($this->getData('is_daily_work_time')
            && resolve_static(WorkTime::class, 'query')
                ->when(
                    $this->getData('employee_id'),
                    fn (Builder $query) => $query->where('employee_id', $this->getData('employee_id'))
                )
                ->when(
                    $this->getData('user_id'),
                    fn (Builder $query) => $query->where('user_id', $this->getData('user_id'))
                )
                ->where(function (Builder $query): void {
                    $query->where('started_at', '<=', $this->getData('ended_at'))
                        ->where('ended_at', '>=', $this->getData('started_at'))
                        ->orWhere(fn (Builder $query) => $query
                            ->where('started_at', '<=', $this->getData('started_at'))
                            ->whereNull('ended_at')
                        );
                })
                ->where('is_daily_work_time', true)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'started_at' => ['Daily work time already started in given timeframe.'],
            ])
                ->errorBag('createLockedWorkTime');
        }

        if ($endedAt = data_get($this->data, 'ended_at')) {
            $totalTimeMs = Carbon::parse($this->data['started_at'])->diffInMilliseconds(Carbon::parse($endedAt), true)
                - data_get($this->data, 'paused_time_ms', 0);

            if ($totalTimeMs < 0) {
                throw ValidationException::withMessages([
                    'paused_time_ms' => ['Pause can not be longer than time between started_at and ended_at.'],
                ])
                    ->errorBag('createLockedWorkTime');
            }
        }
    }
}
