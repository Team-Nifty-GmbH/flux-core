<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateLockedWorkTimeRequest;
use FluxErp\Models\WorkTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

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

    public function performAction(): Model
    {
        $workTime = WorkTime::query()
            ->whereKey($this->data['id'])
            ->first();

        $workTime->fill($this->data);

        if (! data_get($this->data, 'ended_at')) {
            $workTime->total_time_ms = 0;
            $workTime->is_locked = false;
        } else {
            $workTime->total_time_ms = Carbon::parse($this->data['ended_at'])
                ->diffInMilliseconds(Carbon::parse($workTime->started_at)) - $workTime->paused_time_ms;
        }

        $workTime->save();

        return $workTime;
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($endedAt = data_get($this->data, 'ended_at')) {
            $workTime = WorkTime::query()
                ->whereKey($this->data['id'])
                ->first();

            $totalTimeMs = Carbon::parse($endedAt)->diffInMilliseconds(Carbon::parse($this->data['started_at']))
                - data_get($this->data, 'paused_time_ms', $workTime->paused_time_ms);

            if ($totalTimeMs < 0) {
                throw ValidationException::withMessages([
                    'paused_time_ms' => [__('Pause can not be longer than time between started_at and ended_at.')],
                ])->errorBag('updateLockedWorkTime');
            }
        }
    }
}