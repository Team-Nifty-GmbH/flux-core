<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Rulesets\User\CreateUserRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

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
        $defaultMailAccountId = Arr::pull($this->data, 'default_mail_account_id');
        $printers = Arr::pull($this->data, 'printers');

        $user = app(User::class, ['attributes' => $this->data]);
        $user->save();

        if ($mailAccounts) {
            $user->mailAccounts()->attach(
                collect($mailAccounts)
                    ->mapWithKeys(fn (int|string $mailAccountId) => [
                        $mailAccountId => ['is_default' => $mailAccountId == $defaultMailAccountId],
                    ])
                    ->all()
            );
        }

        if ($printers) {
            $user->printers()->attach($printers);
        }

        return $user->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['is_active'] ??= true;
        $this->data['language_id'] ??= resolve_static(Language::class, 'default')?->getKey();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->getData('default_mail_account_id')) {
            $mailAccounts = $this->getData('mail_accounts') ?? [];

            if (! in_array($this->getData('default_mail_account_id'), $mailAccounts, true)) {
                throw ValidationException::withMessages([
                    'default_mail_account_id' => [
                        'The default mail account must be one of the selected mail accounts.',
                    ],
                ])
                    ->errorBag('createUser');
            }
        }
    }
}
