<?php

namespace FluxErp\Actions\LedgerAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rulesets\LedgerAccount\DeleteLedgerAccountRuleset;

class DeleteLedgerAccount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteLedgerAccountRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [LedgerAccount::class];
    }

    public function performAction(): mixed
    {
        return resolve_static(LedgerAccount::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
