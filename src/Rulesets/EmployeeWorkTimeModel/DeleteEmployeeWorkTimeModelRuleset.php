<?php

namespace FluxErp\Rulesets\EmployeeWorkTimeModel;

use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteEmployeeWorkTimeModelRuleset extends FluxRuleset
{
    protected static ?string $model = EmployeeWorkTimeModel::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => EmployeeWorkTimeModel::class]),
            ],
        ];
    }
}
