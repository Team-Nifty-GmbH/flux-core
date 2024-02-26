<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rules\ModelExists;

class DeletePurchaseInvoice extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => [
                'required',
                'integer',
                new ModelExists(PurchaseInvoice::class),
            ],
        ];
    }

    public static function models(): array
    {
        return [PurchaseInvoice::class];
    }

    public function performAction(): ?bool
    {
        return PurchaseInvoice::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
