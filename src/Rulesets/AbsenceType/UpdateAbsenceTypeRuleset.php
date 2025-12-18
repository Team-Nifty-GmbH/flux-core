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
            'employee_can_create' => [
                'required',
                Rule::enum(EmployeeCanCreateEnum::class),
            ],
            'affects_overtime' => [
                'boolean',
                'required_with:affects_sick_leave,affects_vacation',
                'declined_if:affects_sick_leave,true',
                'declined_if:affects_vacation,true',
            ],
            'affects_sick_leave' => [
                'boolean',
                'required_with:affects_overtime,affects_vacation',
                'declined_if:affects_overtime,true',
                'declined_if:affects_vacation,true',
            ],
            'affects_vacation' => [
                'boolean',
                'required_with:affects_overtime,affects_sick_leave',
                'declined_if:affects_overtime,true',
                'declined_if:affects_sick_leave,true',
            ],
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
