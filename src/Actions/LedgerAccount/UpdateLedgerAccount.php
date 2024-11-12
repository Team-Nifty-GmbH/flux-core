<?php

namespace FluxErp\Actions\LedgerAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rulesets\LedgerAccount\UpdateLedgerAccountRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateLedgerAccount extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateLedgerAccountRuleset::class;
    }

    public static function models(): array
    {
        return [LedgerAccount::class];
    }

    public function performAction(): Model
    {
        $ledgerAccount = resolve_static(LedgerAccount::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $ledgerAccount->fill($this->data);
        $ledgerAccount->save();

        return $ledgerAccount->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['number'] .= ',' . ($this->data['id'] ?? 0);
    }
}
