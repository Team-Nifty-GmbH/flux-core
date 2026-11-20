<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\WorkTime\CreateWorkTimeRuleset;
use Illuminate\Validation\ValidationException;

class CreateWorkTime extends FluxAction
{
    public static function models(): array
    {
        return [WorkTime::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateWorkTimeRuleset::class;
    }

    public function performAction(): WorkTime
    {
        $workTime = app(WorkTime::class, ['attributes' => $this->data]);

        if (is_null(data_get($this->data, 'is_billable'))) {
            $workTime->is_billable = $workTime->workTimeType?->is_billable ?? false;
        }

        $workTime->save();

        return $workTime->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['user_id'] = auth()->user()->id;
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (! $this->getData('is_pause')
            || ! $this->getData('is_daily_work_time')
            || $this->getData('ended_at')
        ) {
            return;
        }

        $hasOpenPause = resolve_static(WorkTime::class, 'query')
            ->where('user_id', $this->getData('user_id'))
            ->where('is_daily_work_time', true)
            ->where('is_locked', false)
            ->where('is_pause', true)
            ->exists();

        if ($hasOpenPause) {
            throw ValidationException::withMessages([
                'is_pause' => [__('A pause is already running.')],
            ])->errorBag('createWorkTime');
        }
    }
}
