<?php

namespace FluxErp\Rulesets\EmployeeDay;

use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\Holiday;
use FluxErp\Models\WorkTime;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateEmployeeDayRuleset extends FluxRuleset
{
    protected static ?string $model = EmployeeDay::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => EmployeeDay::class]),
            ],
            'holiday_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Holiday::class]),
            ],
            'date' => 'sometimes|required|date',
            'target_hours' => [
                'sometimes',
                'required',
                app(Numeric::class, ['min' => 0, 'max' => 24]),
            ],
            'actual_hours' => [
                'sometimes',
                'required',
                app(Numeric::class, ['min' => 0, 'max' => 24]),
            ],
            'break_minutes' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 1440]),
            ],
            'sick_days_used' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 1]),
            ],
            'sick_hours_used' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 24]),
            ],
            'vacation_days_used' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 1]),
            ],
            'vacation_hours_used' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 24]),
            ],
            'plus_minus_absence_hours' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 24]),
            ],
            'plus_minus_overtime_hours' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 24]),
            ],
            'is_holiday' => 'boolean',
            'is_work_day' => 'boolean',

            'absence_requests' => 'nullable|array',
            'absence_requests.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AbsenceRequest::class]),
            ],
            'work_times' => 'nullable|array',
            'work_times.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTime::class])
                    ->where('is_daily_work_time', true),
            ],
        ];
    }
}
