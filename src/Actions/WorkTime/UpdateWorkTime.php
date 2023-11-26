<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateWorkTimeRequest;
use FluxErp\Models\WorkTime;
use Illuminate\Database\Eloquent\Model;

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

        if (($workTime->ended_at && is_null($this->data['ended_at']))
            || $workTime->ended_at && $this->data['ended_at'] && $this->data['is_locked']
        ) {
            $this->data['paused_time'] = $workTime->paused_time + $workTime->ended_at->diffInSeconds(now());
        }

        $workTime->fill($this->data);

        if ($this->data['is_locked']) {
            $workTime->total_time = $workTime->total_time + $workTime->ended_at->diffInSeconds($workTime->started_at) - $workTime->paused_time;
        }

        $workTime->save();

        if ($workTime->is_daily_work_time && $workTime->is_locked) {
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
}
