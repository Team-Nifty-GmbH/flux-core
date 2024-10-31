<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\WorkTime\CreateWorkTimeRuleset;

class CreateWorkTime extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateWorkTimeRuleset::class;
    }

    public static function models(): array
    {
        return [WorkTime::class];
    }

    public function performAction(): WorkTime
    {
        $workTime = app(WorkTime::class, ['attributes' => $this->data]);

        if (is_null(data_get($this->data, 'is_billable'))) {
            $workTime->is_billable = $workTime->workTimeType?->is_billable ?? false;
        }

        $workTime->save();

        return $workTime->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['user_id'] = auth()->user()->id;
    }
}
