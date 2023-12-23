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

    public ?int $language_id = null;

    public ?int $currency_id = null;

    public ?string $email = null;

    public ?string $firstname = null;

    public ?string $lastname = null;

    public ?string $password = null;

    public ?string $password_confirmation = null;

    public ?string $user_code = null;

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
