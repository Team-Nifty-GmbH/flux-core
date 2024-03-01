<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Rulesets\User\CreateUserRuleset;
use Illuminate\Support\Arr;

class CreateUser extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateUserRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function performAction(): User
    {
        $mailAccounts = Arr::pull($this->data, 'mail_accounts');

        $this->data['is_active'] = $this->data['is_active'] ?? true;
        $this->data['language_id'] = $this->data['language_id'] ??
            resolve_static(Language::class, 'default')?->id;

        $user = app(User::class, ['attributes' => $this->data]);
        $user->save();

        if ($mailAccounts) {
            $user->mailAccounts()->attach($mailAccounts);
        }

        return $user->refresh();
    }
}
