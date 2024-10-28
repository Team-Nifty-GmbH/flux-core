<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\BankConnection;
use FluxErp\Rulesets\BankConnection\DeleteBankConnectionRuleset;

class DeleteBankConnection extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteBankConnectionRuleset::class;
    }

    public static function models(): array
    {
        return [BankConnection::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(BankConnection::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
