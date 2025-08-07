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

    public ?string $bank_name = null;

    public ?string $bic = null;

    public array $clients = [];

    public ?string $color = null;

    public ?int $contact_id = null;

    public ?float $cost_per_hour = null;

    public ?int $currency_id = null;

    public ?string $date_of_birth = null;

    public ?string $email = null;

    public ?string $employee_number = null;

    public ?string $employment_date = null;

    public ?string $termination_date = null;

    public ?string $firstname = null;

    public ?string $iban = null;

    #[Locked]
    public ?int $id = null;

    // HR Fields
    public ?int $work_time_model_id = null;

    public ?int $location_id = null;

    public ?int $supervisor_id = null;

    public ?string $birth_date = null;

    public ?string $social_security_number = null;

    public ?string $tax_id = null;

    public ?string $tax_class = null;

    public ?float $salary = null;

    public ?string $salary_type = null;

    public ?float $vacation_days_current = null;

    public ?float $vacation_days_carried = null;

    public ?float $overtime_hours = null;

    public ?string $emergency_contact_name = null;

    public ?string $emergency_contact_phone = null;

    public ?string $emergency_contact_relation = null;

    public bool $is_active = false;

    public bool $is_dark_mode = false;

    public ?int $language_id = null;

    public ?string $lastname = null;

    public array $mail_accounts = [];

    public ?int $parent_id = null;

    public ?string $password = null;

    public ?string $password_confirmation = null;

    public array $permissions = [];

    public ?string $phone = null;

    public ?array $printers = [];

    public array $roles = [];

    public ?string $timezone = null;

    public ?string $user_code = null;

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

    protected static function getModel(): string
    {
        return User::class;
    }
}
