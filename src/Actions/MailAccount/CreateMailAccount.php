<?php

namespace FluxErp\Actions\MailAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailAccount;
use FluxErp\Rulesets\MailAccount\CreateMailAccountRuleset;

class CreateMailAccount extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateMailAccountRuleset::class;
    }

    public static function models(): array
    {
        return [MailAccount::class];
    }

    public function performAction(): MailAccount
    {
        $mailAccount = app(MailAccount::class, ['attributes' => $this->data]);
        $mailAccount->save();

        return $mailAccount->refresh();
    }
}
