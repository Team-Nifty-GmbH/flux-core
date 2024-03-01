<?php

namespace FluxErp\Actions\MailAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailAccount;
use FluxErp\Rulesets\MailAccount\CreateMailAccountRuleset;

class CreateMailAccount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateMailAccountRuleset::class, 'getRules');
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
