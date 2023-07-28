<?php

namespace FluxErp\Actions\Account;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Account;

class DeleteAccount extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:accounts,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Account::class];
    }

    public function performAction(): ?bool
    {
        return Account::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
