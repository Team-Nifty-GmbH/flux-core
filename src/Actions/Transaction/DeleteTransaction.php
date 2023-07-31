<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Transaction;

class DeleteTransaction extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:transactions,id',
        ];
    }

    public static function models(): array
    {
        return [Transaction::class];
    }

    public function execute(): ?bool
    {
        return Transaction::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
