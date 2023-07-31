<?php

namespace FluxErp\Actions\Account;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Account;

class DeleteAccount extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:accounts,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Account::class];
    }

    public function execute(): ?bool
    {
        return Account::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
