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
                if (! isset($week['days']) || ! is_array($week['days'])) {
                    continue;
                }

                foreach ($week['days'] as $day => $dayData) {
                    if (! $dayData || ! is_array($dayData)) {
                        continue;
                    }

                    if ($dayData['start_time'] && $dayData['end_time']) {
                        $workTimeModel->schedules()->create([
                            'week_number' => $week['week_number'] ?? 1,
                            'weekday' => $dayData['weekday'] ?? $day,
                            'start_time' => $dayData['start_time'],
                            'end_time' => $dayData['end_time'],
                            'work_hours' => abs((float) ($dayData['work_hours'] ?? 0)),
                            'break_minutes' => abs((int) ($dayData['break_minutes'] ?? 0)),
                        ]);
                    }
                }
            }
        }

        return $workTimeModel->fresh('schedules');
    }
}
