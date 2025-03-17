<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\BankConnection;
use FluxErp\Rulesets\BankConnection\DeleteBankConnectionRuleset;

class DeleteBankConnection extends FluxAction
{
    public static function models(): array
    {
        return [BankConnection::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteBankConnectionRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(BankConnection::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
