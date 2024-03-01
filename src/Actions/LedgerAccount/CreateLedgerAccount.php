<?php

namespace FluxErp\Actions\LedgerAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rulesets\LedgerAccount\CreateLedgerAccountRuleset;

class CreateLedgerAccount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateLedgerAccountRuleset::class, 'getRules');
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
