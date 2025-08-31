<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\User\CreateUser;
use FluxErp\Actions\User\DeleteUser;
use FluxErp\Actions\User\UpdateUser;
use FluxErp\Models\User;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class UserForm extends FluxForm
{
    use SupportsAutoRender;

    public ?string $account_holder = null;

    public ?int $additional_vacation_days = null;

    public ?string $bank_name = null;

    public ?float $base_salary = null;

    public ?string $bic = null;

    public array $clients = [];

    public ?string $color = null;

    public ?string $confession = null;

    public ?int $contact_id = null;

    public ?float $cost_per_hour = null;

    public ?int $currency_id = null;

    public ?string $date_of_birth = null;

    public ?string $email = null;

    public ?string $emergency_contact_name = null;

    public ?string $emergency_contact_phone = null;

    public ?string $emergency_contact_relation = null;

    public ?string $employee_number = null;

    public ?int $employer_client_id = null;

    public ?string $employment_date = null;

    public ?string $firstname = null;

    public ?string $fixed_term_contract_until = null;

    public ?string $health_insurance = null;

    public ?string $health_insurance_member_number = null;

    public ?string $hire_date = null;

    public ?float $hourly_rate = null;

    public ?string $iban = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = false;

    public bool $is_dark_mode = false;

    public ?string $job_title = null;

    public ?int $language_id = null;

    public ?string $lastname = null;

    public ?int $location_id = null;

    public array $mail_accounts = [];

    // phone Feld existiert bereits weiter oben in der Klasse

    public ?string $mobile_phone = null;

    public ?string $name = null;

    // Zusätzliche HR Felder
    public ?string $nationality = null;

    public ?int $number_of_children = null;

    public ?float $overtime_hours = null;

    public ?int $parent_id = null;

    public ?string $password = null;

    public ?string $password_confirmation = null;

    public ?string $payment_frequency = null;

    public array $permissions = [];

    public ?string $phone = null;

    public ?string $place_of_birth = null;

    public ?int $previous_year_vacation_days = null;

    public ?array $printers = [];

    public ?string $private_email = null;

    public ?string $probation_period_until = null;

    public ?string $residence_permit_until = null;

    public array $roles = [];

    public ?float $salary = null;

    public ?string $salary_type = null;

    // date_of_birth existiert bereits weiter oben in der Klasse

    public ?string $social_security_number = null;

    public ?string $street_and_house_number = null;

    public ?int $supervisor_id = null;

    public ?string $tax_class = null;

    public ?string $tax_id = null;

    public ?string $termination_date = null;

    public ?string $timezone = null;

    public ?string $user_code = null;

    public ?int $user_department_id = null;

    public ?float $vacation_days_carried = null;

    public ?float $vacation_days_current = null;

    public ?string $work_permit_until = null;

    // HR Fields
    public ?int $work_time_model_id = null;

    public ?int $yearly_vacation_days = null;

    protected static function getModel(): string
    {
        return User::class;
    }

    public function validateSave($rules = null, $messages = [], $attributes = []): void
    {
        parent::validateSave(['password' => 'required|confirmed'], $messages, $attributes);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateUser::class,
            'update' => UpdateUser::class,
            'delete' => DeleteUser::class,
        ];
    }
}
