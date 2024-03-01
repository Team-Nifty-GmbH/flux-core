<?php

namespace FluxErp\Actions\ContactBankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rulesets\ContactBankConnection\CreateContactBankConnectionRuleset;

class CreateContactBankConnection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateContactBankConnectionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ContactBankConnection::class];
    }

    public function performAction(): ContactBankConnection
    {
        $contactBankConnection = app(ContactBankConnection::class, ['attributes' => $this->data]);
        $contactBankConnection->save();

        return $contactBankConnection->refresh();
    }
}
