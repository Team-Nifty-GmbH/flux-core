<?php

namespace FluxErp\Actions\WorkTimeModel;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rulesets\WorkTimeModel\UpdateWorkTimeModelRuleset;
use Illuminate\Support\Arr;

class UpdateWorkTimeModel extends FluxAction
{
    public static function models(): array
    {
        return [WorkTimeModel::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateWorkTimeModelRuleset::class;
    }

    public function performAction(): WorkTimeModel
    {
        $workTimeModel = resolve_static(WorkTimeModel::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $data = $this->getData();
        $schedules = Arr::pull($data, 'schedules');

        $workTimeModel->fill($data);
        $workTimeModel->save();

        if (is_array($schedules)) {
            $workTimeModel->schedules()->delete();

            foreach ($schedules as $week) {
                foreach (data_get($week, 'days') ?? [] as $day) {
                    if (bccomp(data_get($day, 'work_hours'), 0) === 0) {
                        continue;
                    }

                    $workTimeModel->schedules()->create(
                        array_merge(
                            $day,
                            [
                                'week_number' => data_get($week, 'week_number') ?? 1,
                            ]
                        )
                    );
                }
            }
        }

        return $workTimeModel->fresh('schedules');
    }
}
