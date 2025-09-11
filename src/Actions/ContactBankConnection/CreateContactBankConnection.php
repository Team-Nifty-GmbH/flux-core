<?php

namespace FluxErp\Actions\ContactBankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rulesets\ContactBankConnection\CreateContactBankConnectionRuleset;
use Illuminate\Support\Str;

class CreateContactBankConnection extends FluxAction
{
    public static function models(): array
    {
        return [ContactBankConnection::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateContactBankConnectionRuleset::class;
    }

    public function performAction(): ContactBankConnection
    {
        if ($this->getData('is_credit_account')) {
            $this->data['balance'] = 0;
        }

        $contactBankConnection = app(ContactBankConnection::class, ['attributes' => $this->data]);
        $contactBankConnection->save();

        return $contactBankConnection->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['iban'] = is_string($this->getData('iban'))
            ? Str::of($this->getData('iban'))->upper()->remove(' ')->toString()
            : $this->getData('iban');

        $this->data['bic'] = is_string($this->getData('bic'))
            ? Str::of($this->getData('bic'))->upper()->remove(' ')->toString()
            : $this->getData('bic');
    }
}
