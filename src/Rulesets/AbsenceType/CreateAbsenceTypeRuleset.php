<?php

namespace FluxErp\Rulesets\AbsenceType;

use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Models\AbsencePolicy;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateAbsenceTypeRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:absence_types,code',
            'color' => 'required|hex_color',
            'percentage_deduction' => [
                'required',
                app(Numeric::class, ['min' => 0, 'max' => 1]),
            ],
            'employee_can_create' => [
                'required',
                Rule::enum(EmployeeCanCreateEnum::class),
            ],
            'affects_overtime' => [
                'boolean',
                'declined_if:affects_sick_leave,true',
                'declined_if:affects_vacation,true',
            ],
            'affects_sick_leave' => [
                'boolean',
                'declined_if:affects_overtime,true',
                'declined_if:affects_vacation,true',
            ],
            'affects_vacation' => [
                'boolean',
                'declined_if:affects_overtime,true',
                'declined_if:affects_sick_leave,true',
            ],
            'is_active' => 'boolean',

            'absence_policies' => 'nullable|array',
            'absence_policies.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AbsencePolicy::class]),
            ],
        ];
    }
}
