<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Transaction;

class DeleteTransaction extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:transactions,id',
        ];
    }

    public static function models(): array
    {
        return [Transaction::class];
    }

    public function performAction(): ?bool
    {
        return Transaction::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
