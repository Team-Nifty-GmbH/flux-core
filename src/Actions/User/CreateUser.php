<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Rulesets\User\CreateUserRuleset;
use Illuminate\Support\Arr;

class CreateUser extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateUserRuleset::class;
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
            Language::default()?->getKey();

        $user = app(User::class, ['attributes' => $this->data]);
        $user->save();

        if ($mailAccounts) {
            $user->mailAccounts()->attach($mailAccounts);
        }

        return $user->refresh();
    }
}
