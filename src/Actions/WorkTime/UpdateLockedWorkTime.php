<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\WorkTime\UpdateLockedWorkTimeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class UpdateLockedWorkTime extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateLockedWorkTimeRuleset::class;
    }

    public static function models(): array
    {
        return [WorkTime::class];
    }

    public function performAction(): Model
    {
        $workTime = resolve_static(WorkTime::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $workTime->fill($this->data);

        if (array_key_exists('ended_at', $this->data)) {
            if (! $this->getData('ended_at')) {
                $workTime->total_time_ms = 0;
                $workTime->is_locked = false;
            } else {
                $workTime->total_time_ms = bcsub(
                    Carbon::parse($workTime->started_at)
                        ->diffInMilliseconds(Carbon::parse($this->getData('ended_at'))),
                    $workTime->paused_time_ms ?? 0,
                    0
                );

                if ($workTime->is_pause) {
                    $workTime->total_time_ms = bcmul($workTime->total_time_ms, -1, 0);
                }
            }
        }

        $workTime->save();

        return $workTime;
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($endedAt = $this->getData('ended_at')) {
            $workTime = resolve_static(WorkTime::class, 'query')
                ->whereKey($this->getData('id'))
                ->first();

            $totalTimeMs = bcsub(
                Carbon::parse($this->getData('started_at'))->diffInMilliseconds(Carbon::parse($endedAt)),
                $this->getData('paused_time_ms', $workTime->paused_time_ms ?? 0),
            );

            if (bccomp($totalTimeMs, 0) === -1) {
                throw ValidationException::withMessages([
                    'paused_time_ms' => [__('Pause can not be longer than time between started_at and ended_at.')],
                ])->errorBag('updateLockedWorkTime');
            }
        }
    }
}
