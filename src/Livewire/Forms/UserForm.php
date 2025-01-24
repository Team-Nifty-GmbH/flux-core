<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\User\CreateUser;
use FluxErp\Actions\User\DeleteUser;
use FluxErp\Actions\User\UpdateUser;
use Livewire\Attributes\Locked;

class UserForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $contact_id = null;

    public ?int $language_id = null;

    public ?int $parent_id = null;

    public ?int $currency_id = null;

    public ?string $email = null;

    public ?string $firstname = null;

    public ?string $lastname = null;

    public ?string $phone = null;

    public ?string $password = null;

    public ?string $password_confirmation = null;

    public ?string $user_code = null;

    public ?string $timezone = null;

    public ?string $color = null;

    public ?string $date_of_birth = null;

    public ?string $employee_number = null;

    public ?string $employment_date = null;

    public ?string $termination_date = null;

    public ?string $iban = null;

    public ?string $account_holder = null;

    public ?string $bank_name = null;

    public ?string $bic = null;

    public ?float $cost_per_hour = null;

    public bool $is_active = false;

    public bool $is_dark_mode = false;

    public array $roles = [];

    public array $permissions = [];

    public array $clients = [];

    public array $mail_accounts = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateUser::class,
            'update' => UpdateUser::class,
            'delete' => DeleteUser::class,
        ];
    }

    public function validateSave($rules = null, $messages = [], $attributes = []): void
    {
        parent::validateSave(['password' => 'required|confirmed'], $messages, $attributes);
    }
}
