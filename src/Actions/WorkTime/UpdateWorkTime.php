<?php

namespace FluxErp\Actions\WorkTime;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateWorkTimeRequest;
use FluxErp\Models\WorkTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateWorkTime extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateWorkTimeRequest())->rules();
    }

    public static function models(): array
    {
        return [WorkTime::class];
    }

    public function performAction(): Model
    {
        $workTime = WorkTime::query()
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

        $workTime->fill($this->data);

        if ($this->data['is_locked']) {
            $workTime->total_time_ms =
                $workTime->ended_at->diffInSeconds($workTime->started_at) * 1000 -
                $workTime->paused_time_ms;
        }

        $workTime->save();

        if ($workTime->is_daily_work_time && $workTime->is_locked && ! $workTime->is_pause) {
            // end all active work times for this user
            WorkTime::query()
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

        return $workTime->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $workTime = WorkTime::query()
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
