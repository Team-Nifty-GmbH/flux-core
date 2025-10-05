<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\Employee\DeleteEmployee;
use FluxErp\Actions\Employee\UpdateEmployee;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class EmployeeForm extends FluxForm
{
    use SupportsAutoRender;

    public ?string $account_holder = null;

    public ?string $bank_name = null;

    public ?float $base_salary = null;

    public ?string $bic = null;

    public ?string $city = null;

    public ?int $client_id = null;

    public ?string $confession = null;

    public ?string $date_of_birth = null;

    public ?string $email = null;

    public ?int $employee_department_id = null;

    public ?string $employee_number = null;

    public ?string $employment_date = null;

    public ?string $firstname = null;

    public ?string $fixed_term_contract_until = null;

    public ?string $health_insurance = null;

    public ?string $health_insurance_member_number = null;

    public ?float $hourly_rate = null;

    public ?string $iban = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?string $job_title = null;

    public ?string $lastname = null;

    public ?int $location_id = null;

    public ?string $nationality = null;

    public float $number_of_children = 0;

    public ?string $payment_interval = null;

    public ?string $phone = null;

    public ?string $phone_mobile = null;

    public ?string $place_of_birth = null;

    public ?string $probation_period_until = null;

    public ?string $residence_permit_until = null;

    public ?string $salary_type = null;

    public ?string $salutation = null;

    public ?string $social_security_number = null;

    public ?string $street = null;

    public ?int $supervisor_id = null;

    public ?string $tax_identification_number = null;

    public ?string $termination_date = null;

    public ?int $user_id = null;

    public ?int $vacation_carryover_rule_id = null;

    public ?string $work_permit_until = null;

    public ?int $work_time_model_id = null;

    public ?string $zip = null;

    public function fill($values): void
    {
        parent::fill($values);

        if ($values->date_of_birth) {
            $this->date_of_birth = $values->date_of_birth->format('Y-m-d');
        }

        if ($values->employment_date) {
            $this->employment_date = $values->employment_date->format('Y-m-d');
        }

        if ($values->termination_date) {
            $this->termination_date = $values->termination_date->format('Y-m-d');
        }

        if ($values->probation_period_until) {
            $this->probation_period_until = $values->probation_period_until->format('Y-m-d');
        }

        if ($values->fixed_term_contract_until) {
            $this->fixed_term_contract_until = $values->fixed_term_contract_until->format('Y-m-d');
        }

        if ($values->work_permit_until) {
            $this->work_permit_until = $values->work_permit_until->format('Y-m-d');
        }

        if ($values->residence_permit_until) {
            $this->residence_permit_until = $values->residence_permit_until->format('Y-m-d');
        }
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateEmployee::class,
            'update' => UpdateEmployee::class,
            'delete' => DeleteEmployee::class,
        ];
    }
}
