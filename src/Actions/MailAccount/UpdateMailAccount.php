<?php

namespace FluxErp\Actions\MailAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\MailAccount;
use FluxErp\Rulesets\MailAccount\UpdateMailAccountRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateMailAccount extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateMailAccountRuleset::class;
    }

    public static function models(): array
    {
        return [MailAccount::class];
    }

    public function performAction(): Model
    {
        if (array_key_exists('password', $this->data) && is_null($this->data['password'])) {
            unset($this->data['password']);
        }

        $mailAccount = resolve_static(MailAccount::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $mailAccount->fill($this->data);
        $mailAccount->save();

        return $mailAccount->withoutRelations()->fresh();
    }
}
