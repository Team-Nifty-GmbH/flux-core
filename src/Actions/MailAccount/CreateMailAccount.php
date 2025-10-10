<?php

namespace FluxErp\Actions\MailAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailAccount;
use FluxErp\Rulesets\MailAccount\CreateMailAccountRuleset;

class CreateMailAccount extends FluxAction
{
    public static function models(): array
    {
        return [MailAccount::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateMailAccountRuleset::class;
    }

    public function performAction(): MailAccount
    {
        $mailAccount = app(MailAccount::class, ['attributes' => $this->data]);
        $mailAccount->save();

        return $mailAccount->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['name'] ??= $this->getData('smtp_email') ?? $this->getData('email');
    }
}
