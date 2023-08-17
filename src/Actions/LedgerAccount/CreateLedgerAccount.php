<?php

namespace FluxErp\Actions\LedgerAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateLedgerAccountRequest;
use FluxErp\Models\LedgerAccount;

class CreateLedgerAccount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateLedgerAccountRequest())->rules();
    }

    public static function models(): array
    {
        return [LedgerAccount::class];
    }

    public function performAction(): mixed
    {
        $ledgerAccount = new LedgerAccount($this->data);
        $ledgerAccount->save();

        return $ledgerAccount->fresh();
    }
}
