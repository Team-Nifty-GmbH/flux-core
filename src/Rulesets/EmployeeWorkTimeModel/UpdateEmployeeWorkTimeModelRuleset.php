<?php

namespace FluxErp\Rulesets\EmployeeWorkTimeModel;

use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateEmployeeWorkTimeModelRuleset extends FluxRuleset
{
    protected static ?string $model = EmployeeWorkTimeModel::class;

    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => EmployeeWorkTimeModel::class]),
            ],
            'valid_from' => 'sometimes|required|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'annual_vacation_days' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 365]),
            ],
            'note' => 'nullable|string',
        ];
    }
}
