<?php

namespace FluxErp\Actions\ContactBankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactBankConnection;

class DeleteContactBankConnection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:contact_bank_connections,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [ContactBankConnection::class];
    }

    public function performAction(): ?bool
    {
        return ContactBankConnection::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
