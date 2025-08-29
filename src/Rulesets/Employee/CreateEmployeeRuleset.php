<?php

namespace FluxErp\Rulesets\Employee;

use FluxErp\Enums\SalutationEnum;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\Location;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateEmployeeRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'salutation' => [
                'nullable',
                Rule::enum(SalutationEnum::class),
            ],
            'employee_number' => 'nullable|string|max:255',

            'employee_department_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => EmployeeDepartment::class]),
            ],
            'location_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Location::class]),
            ],
            'user_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string|max:255',
            'place_of_birth' => 'nullable|string|max:255',
            'confession' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'mobile_phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'social_security_number' => 'nullable|string|max:255',
            'tax_identification_number' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'employment_date' => 'nullable|date',
            'termination_date' => 'nullable|date',
            'probation_period_until' => 'nullable|date',
            'fixed_term_contract_until' => 'nullable|date',
            'work_permit_until' => 'nullable|date',
            'residence_permit_until' => 'nullable|date',
            'base_salary' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|string|in:hourly,monthly,annual',
            'payment_interval' => 'nullable|string|in:weekly,biweekly,monthly,quarterly,annual',
            'hourly_rate' => 'nullable|numeric|min:0',
            'health_insurance' => 'nullable|string|max:255',
            'health_insurance_member_number' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34',
            'account_holder' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bic' => 'nullable|string|max:11',
            'number_of_children' => 'integer|min:0',
            'is_active' => 'boolean',
        ];
    }
}
