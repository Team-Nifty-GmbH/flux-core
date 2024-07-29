<?php

namespace FluxErp\Actions\ContactBankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rulesets\ContactBankConnection\DeleteContactBankConnectionRuleset;

class DeleteContactBankConnection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteContactBankConnectionRuleset::class, 'getRules');
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
