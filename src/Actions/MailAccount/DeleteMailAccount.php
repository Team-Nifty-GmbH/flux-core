<?php

namespace FluxErp\Actions\MailAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailAccount;
use FluxErp\Rulesets\MailAccount\DeleteMailAccountRuleset;

class DeleteMailAccount extends FluxAction
{
    public static function models(): array
    {
        return [MailAccount::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteMailAccountRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(MailAccount::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
