<?php

namespace FluxErp\Actions\WorkTime;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\WorkTime\UpdateWorkTimeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateWorkTime extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateWorkTimeRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [WorkTime::class];
    }

    public function performAction(): Model
    {
        $workTime = app(WorkTime::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        if (! $workTime->is_daily_work_time
            && $workTime->ended_at
            && array_key_exists('ended_at', $this->data)
            && Carbon::parse($this->data['ended_at'])->notEqualTo($workTime->ended_at)
        ) {
            $endedAt = match (true) {
                is_null($this->data['ended_at']) => $workTime->ended_at,
                default => Carbon::parse($this->data['ended_at'])
            };

            if ($endedAt->lt($workTime->ended_at)) {
                $this->data['paused_time_ms'] = $workTime->paused_time_ms -
                    $endedAt->diffInSeconds($workTime->ended_at) * 1000;
            } else {
                $this->data['paused_time_ms'] = $workTime->paused_time_ms +
                    $workTime->ended_at->diffInSeconds(now()) * 1000;
            }
        }

        if (is_null(data_get($this->data, 'is_billable')) && array_key_exists('is_billable', $this->data)) {
            unset($this->data['is_billable']);
        }

        $workTime->fill($this->data);

        if ($workTime->is_daily_work_time && $workTime->is_locked && ! $workTime->is_pause) {
            // if a daily work time pause is currently running delete it
            $pauseTime = app(WorkTime::class)->query()
                ->where('user_id', $workTime->user_id)
                ->where('is_daily_work_time', true)
                ->where('is_locked', false)
                ->where('is_pause', true)
                ->latest()
                ->first();

            if (! is_null($pauseTime)) {
                $workTime->ended_at = $pauseTime->started_at;
                $pauseTime->delete();
            }

            // end all active work times for this user
            app(WorkTime::class)->query()
                ->where('user_id', $workTime->user_id)
                ->where('is_locked', false)
                ->where('id', '!=', $workTime->id)
                ->get()
                ->each(function (WorkTime $workTime) {
                    $workTime->ended_at = now()->toDateTimeString();
                    $workTime->is_locked = true;
                    UpdateWorkTime::make($workTime->toArray())->execute();
                });
        }

        if ($this->data['is_locked']) {
            $workTime->total_time_ms =
                $workTime->ended_at->diffInSeconds($workTime->started_at) * 1000 -
                $workTime->paused_time_ms;
        }

        $workTime->save();

        return $workTime->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $workTime = app(WorkTime::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        if (($this->data['ended_at'] ?? false)
            && $workTime->started_at->gt(Carbon::parse($this->data['ended_at']))
        ) {
            throw ValidationException::withMessages([
                'ended_at' => [__('The ended_at must be a date after :date.', ['date' => $workTime->started_at])],
            ])->errorBag('updateWorkTime');
        }
    }
}
