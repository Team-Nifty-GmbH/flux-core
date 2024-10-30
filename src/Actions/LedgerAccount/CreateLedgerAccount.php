<?php

namespace FluxErp\Actions\LedgerAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rulesets\LedgerAccount\CreateLedgerAccountRuleset;

class CreateLedgerAccount extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateLedgerAccountRuleset::class;
    }

    public static function models(): array
    {
        return [LedgerAccount::class];
    }

    public function performAction(): mixed
    {
        $ledgerAccount = app(LedgerAccount::class, ['attributes' => $this->data]);
        $ledgerAccount->save();

        return $ledgerAccount->fresh();
    }
}
