<?php

namespace FluxErp\Actions\PurchaseInvoicePosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoicePosition;

class DeletePurchaseInvoicePosition extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:purchase_invoice_positions,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [PurchaseInvoicePosition::class];
    }

    public function performAction(): ?bool
    {
        return PurchaseInvoicePosition::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
