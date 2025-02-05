<?php

namespace FluxErp\Actions\LedgerAccount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rulesets\LedgerAccount\UpdateLedgerAccountRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

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

    protected function validateData(): void
    {
        parent::validateData();

        $ledgerAccount = resolve_static(LedgerAccount::class, 'query')
            ->whereKey($this->data['id'])
            ->first(['client_id', 'number', 'ledger_account_type_enum']);

        if (resolve_static(LedgerAccount::class, 'query')
            ->whereKeyNot($this->getData('id'))
            ->where('client_id', $ledgerAccount->client_id)
            ->where('number', $this->getData('number', $ledgerAccount->number))
            ->where('ledger_account_type_enum',
                $this->getData('ledger_account_type_enum', $ledgerAccount->ledger_account_type_enum)
            )
            ->exists()
        ) {
            throw ValidationException::withMessages(['number' => ['The number has already been taken for this type.']]);
        }
    }
}
