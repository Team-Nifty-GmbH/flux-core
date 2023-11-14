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
        $mailAccounts = Arr::pull($this->data, 'mail_accounts', []);

        $this->data['is_active'] = $this->data['is_active'] ?? true;
        $this->data['language_id'] = array_key_exists('language_id', $this->data) ?
            $this->data['language_id'] :
            Language::query()->where('language_code', config('app.locale'))->first()?->id;

        $user = new User($this->data);
        $user->save();

        $user->mailAccounts()->sync($mailAccounts);

        return $user->refresh();
    }
}
