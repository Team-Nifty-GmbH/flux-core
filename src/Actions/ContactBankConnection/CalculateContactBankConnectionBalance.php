<?php

namespace FluxErp\Actions\ContactBankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rulesets\ContactBankConnection\CalculateContactBankConnectionBalanceRuleset;

class CalculateContactBankConnectionBalance extends FluxAction
{
    public static function models(): array
    {
        return [ContactBankConnection::class];
    }

    protected function getRulesets(): string|array
    {
        return CalculateContactBankConnectionBalanceRuleset::class;
    }

    public function performAction(): ContactBankConnection
    {
        $contactBankConnection = resolve_static(ContactBankConnection::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $contactBankConnection->balance = $contactBankConnection->transactions()->sum('amount');
        $contactBankConnection->save();

        return $contactBankConnection->withoutRelations()->fresh();
    }
}
