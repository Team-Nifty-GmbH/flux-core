<?php

namespace FluxErp\Actions\LedgerAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LedgerAccount;

class DeleteLedgerAccount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:ledger_accounts,id',
        ];
    }

    public static function models(): array
    {
        return [LedgerAccount::class];
    }

    public function performAction(): mixed
    {
        return LedgerAccount::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
