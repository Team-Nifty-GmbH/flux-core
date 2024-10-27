<?php

namespace FluxErp\Actions\ContactBankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rulesets\ContactBankConnection\DeleteContactBankConnectionRuleset;

class DeleteContactBankConnection extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteContactBankConnectionRuleset::class;
    }

    public static function models(): array
    {
        return [ContactBankConnection::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(ContactBankConnection::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
