<?php

namespace FluxErp\Rulesets\AbsenceType;

use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Models\AbsencePolicy;
use FluxErp\Models\AbsenceType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateAbsenceTypeRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AbsenceType::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:absence_types,code',
            'color' => 'sometimes|required|hex_color',
            'percentage_deduction' => [
                'sometimes',
                'required',
                app(Numeric::class, ['min' => 0, 'max' => 1]),
            ],
            'employee_can_create_enum' => [
                'required',
                Rule::enum(EmployeeCanCreateEnum::class),
            ],
            'affects_overtime' => 'boolean',
            'affects_sick_leave' => 'boolean',
            'affects_vacation' => 'boolean',
            'is_active' => 'boolean',

            'absence_policies' => 'nullable|array',
            'absence_policies.*' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => AbsencePolicy::class]),
            ],
        ];
    }
}
