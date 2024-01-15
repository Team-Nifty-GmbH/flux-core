<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateLockedWorkTimeRequest;
use FluxErp\Models\WorkTime;
use Illuminate\Support\Carbon;

class UpdateLockedWorkTime extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateLockedWorkTimeRequest())->rules();
    }

    public static function models(): array
    {
        return [WorkTime::class];
    }

    public function performAction(): mixed
    {
        $workTime = WorkTime::query()
            ->whereKey($this->data['id'])
            ->first();

        if (! data_get($this->data, 'ended_at')) {
            $workTime->total_time_ms = 0;
            $workTime->is_locked = false;
        } else {
            $workTime->total_time_ms = Carbon::parse($this->data['ended_at'])
                ->diffInMilliseconds(Carbon::parse($workTime->started_at)) - $workTime->paused_time_ms;
        }

        $workTime->fill($this->data);
        $workTime->save();

        return $workTime;
    }
}
