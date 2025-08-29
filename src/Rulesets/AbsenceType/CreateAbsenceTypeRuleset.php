<?php

namespace FluxErp\Rulesets\AbsenceType;

use FluxErp\Enums\AbsenceRequestCreationTypeEnum;
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
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 1]),
            ],
            'affects_sick' => 'boolean',
            'affects_vacation' => 'boolean',
            'affects_overtime' => 'boolean',
            'is_active' => 'boolean',
            'employee_can_create' => [
                'required',
                Rule::enum(AbsenceRequestCreationTypeEnum::class),
            ],
            'absence_policies' => 'nullable|array',
            'absence_policies.*' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => AbsencePolicy::class]),
            ],
        ];
    }
}
