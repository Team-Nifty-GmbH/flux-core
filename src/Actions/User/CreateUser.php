<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Rulesets\User\CreateUserRuleset;
use Illuminate\Support\Arr;

class CreateUser extends FluxAction
{
    public static function models(): array
    {
        return [User::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateUserRuleset::class;
    }

    public function performAction(): User
    {
        $mailAccounts = Arr::pull($this->data, 'mail_accounts');
        $printers = Arr::pull($this->data, 'printers');

        $user = app(User::class, ['attributes' => $this->data]);
        $user->save();

        if ($mailAccounts) {
            $user->mailAccounts()->attach($mailAccounts);
        }

        if ($printers) {
            $user->printers()->attach($printers);
        }

        return $user->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['is_active'] ??= true;
        $this->data['language_id'] ??= Language::default()?->getKey();
    }
}
