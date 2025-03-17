<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\BankConnection;
use FluxErp\Rulesets\BankConnection\CreateBankConnectionRuleset;

class CreateBankConnection extends FluxAction
{
    public static function models(): array
    {
        return [BankConnection::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateBankConnectionRuleset::class;
    }

    public function performAction(): BankConnection
    {
        $bankConnection = app(BankConnection::class, ['attributes' => $this->data]);
        $bankConnection->save();

        return $bankConnection->fresh();
    }
}
