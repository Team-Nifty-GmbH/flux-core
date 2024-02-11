<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoice;

class DeletePurchaseInvoice extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:purchase_invoices,id,deleted_at,NULL',
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
