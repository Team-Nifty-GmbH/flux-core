<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\BankConnection;
use FluxErp\Rulesets\BankConnection\DeleteBankConnectionRuleset;

class DeleteBankConnection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteBankConnectionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [BankConnection::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(BankConnection::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
