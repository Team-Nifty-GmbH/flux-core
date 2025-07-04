<?php

namespace FluxErp\Actions\ContactBankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rulesets\ContactBankConnection\UpdateContactBankConnectionRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateContactBankConnection extends FluxAction
{
    public static function models(): array
    {
        return [ContactBankConnection::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateContactBankConnectionRuleset::class;
    }

    public function performAction(): Model
    {
        $contactBankConnection = resolve_static(ContactBankConnection::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        // If the bank connection is a credit account, it is a virtual account and therefore does not have an IBAN.
        if ($this->getData('iban') && $contactBankConnection->is_credit_account) {
            unset($this->data['iban']);
        }

        if (! is_null($this->getData('balance')) && ! $contactBankConnection->is_credit_account) {
            unset($this->data['balance']);
        }

        $contactBankConnection->fill($this->data);
        $contactBankConnection->save();

        return $contactBankConnection->withoutRelations()->fresh();
    }
}
