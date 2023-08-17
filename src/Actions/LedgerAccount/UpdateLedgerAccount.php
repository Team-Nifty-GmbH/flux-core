<?php

namespace FluxErp\Actions\LedgerAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateLedgerAccountRequest;
use FluxErp\Models\LedgerAccount;

class UpdateLedgerAccount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateLedgerAccountRequest())->rules();

        $this->rules['number'] = $this->rules['number'] . ',' . $this->data['id'];
    }

    public static function models(): array
    {
        return [LedgerAccount::class];
    }

    public function performAction(): mixed
    {
        $ledgerAccount = LedgerAccount::query()
            ->whereKey($this->data['id'])
            ->first();

        $ledgerAccount->fill($this->data);
        $ledgerAccount->save();

        return $ledgerAccount->withoutRelations()->fresh();
    }
}
