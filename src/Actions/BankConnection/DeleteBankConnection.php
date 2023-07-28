<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\BankConnection;

class DeleteBankConnection extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:bank_connections,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [BankConnection::class];
    }

    public function performAction(): ?bool
    {
        return BankConnection::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
