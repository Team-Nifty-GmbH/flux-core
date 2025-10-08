<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\User\CreateUser;
use FluxErp\Actions\User\DeleteUser;
use FluxErp\Actions\User\UpdateUser;
use Livewire\Attributes\Locked;

class UserForm extends FluxForm
{
    public ?string $account_holder = null;

    public ?string $bank_name = null;

    public ?string $bic = null;

    public array $clients = [];

    public ?string $color = null;

    public ?int $contact_id = null;

    public ?float $cost_per_hour = null;

    public ?int $currency_id = null;

    public ?string $date_of_birth = null;

    public ?int $default_mail_account_id = null;

    public ?string $email = null;

    public ?string $employee_number = null;

    public ?string $employment_date = null;

    public ?string $firstname = null;

    public ?string $iban = null;

    #[Locked]
    public ?int $id = null;

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

    public ?string $termination_date = null;

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
}
