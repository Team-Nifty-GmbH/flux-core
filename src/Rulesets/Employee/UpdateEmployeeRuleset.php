<?php

namespace FluxErp\Rulesets\Employee;

use FluxErp\Enums\SalutationEnum;
use FluxErp\Models\Country;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\Location;
use FluxErp\Models\User;
use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateEmployeeRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'country_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Country::class]),
            ],
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
            'supervisor_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'user_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => User::class])
                    ->whereDoesntHave('employee'),
            ],
            'vacation_carryover_rule_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => VacationCarryoverRule::class]),
            ],
            'salutation' => [
                'nullable',
                Rule::enum(SalutationEnum::class),
            ],
            'firstname' => 'sometimes|required|string|max:255',
            'lastname' => 'sometimes|required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'confession' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'phone_mobile' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'social_security_number' => 'nullable|string|max:255',
            'tax_identification_number' => 'nullable|string|max:255',
            'employee_number' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'employment_date' => 'sometimes|required|date',
            'termination_date' => 'nullable|date|after:employment_date',
            'probation_period_until' => 'nullable|date|after:employment_date',
            'fixed_term_contract_until' => 'nullable|date|after:employment_date',
            'work_permit_until' => 'nullable|date',
            'residence_permit_until' => 'nullable|date',
            'base_salary' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'salary_type' => 'nullable|string|in:hourly,monthly,annual',
            'payment_interval' => 'nullable|string|in:weekly,biweekly,monthly,quarterly,annual',
            'hourly_rate' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'health_insurance' => 'nullable|string|max:255',
            'health_insurance_member_number' => 'nullable|string|max:255',
            'iban' => [
                'nullable',
                'string',
                app(Iban::class),
            ],
            'account_holder' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bic' => [
                'nullable',
                'string',
                'max:255',
            ],
            'number_of_children' => 'integer|min:0',
            'is_active' => 'boolean',
        ];
    }
}
