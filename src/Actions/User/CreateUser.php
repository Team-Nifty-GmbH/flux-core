<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateUserRequest;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use Illuminate\Support\Arr;

class CreateUser extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateUserRequest())->rules();
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function performAction(): User
    {
        $mailAccounts = Arr::pull($this->data, 'mail_accounts');

        $this->data['is_active'] = $this->data['is_active'] ?? true;
        $this->data['language_id'] = $this->data['language_id'] ?? Language::default()?->id;

        $user = new User($this->data);
        $user->save();

        if ($mailAccounts) {
            $user->mailAccounts()->attach($mailAccounts);
        }

        return $user->refresh();
    }
}
