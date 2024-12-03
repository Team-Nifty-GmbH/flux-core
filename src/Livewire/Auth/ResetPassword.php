<?php

namespace FluxErp\Livewire\Auth;

use FluxErp\Actions\User\UpdateUser;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class ResetPassword extends Component
{
    use Actions;

    protected string $updateAction = UpdateUser::class;

    protected string $passwordBroker = 'users';

    #[Url]
    public ?string $email = null;

    #[Url]
    public ?string $token = null;

    #[Rule(['required', 'string', 'confirmed'])]
    public ?string $password = null;

    public ?string $password_confirmation = null;

    public function render(): View
    {
        return view('flux::livewire.auth.reset-password');
    }

    public function resetPassword(): void
    {
        $this->validate();

        $success = false;
        $result = Password::broker($this->passwordBroker)->reset(
            [
                'email' => $this->email,
                'token' => $this->token,
                'password' => $this->password,
            ],
            function (CanResetPassword $user, string $password) use (&$success) {
                $success = $this->updateUser($user, $password);
            }
        );

        if ($result === Password::PASSWORD_RESET && $success) {
            session()->flash('flash.success', __('Password reset successfully'));
        } else {
            session()->flash('flash.error', __('Password reset failed'));
        }

        $this->redirect('/login', true);
    }

    protected function updateUser(CanResetPassword $user, string $password): bool
    {
        try {
            $this->updateAction::make([
                $user->getAuthIdentifierName() => $user->getAuthIdentifier(),
                $user->getAuthPasswordName() => $password,
            ])
                ->validate()
                ->execute();

            return true;
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);
        }

        return false;
    }
}
