<?php

namespace FluxErp\Actions\PurchaseInvoicePosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdatePurchaseInvoicePositionRequest;
use FluxErp\Models\PurchaseInvoicePosition;

class UpdatePurchaseInvoicePosition extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdatePurchaseInvoicePositionRequest())->rules();
    }

    public static function models(): array
    {
        return [PurchaseInvoicePosition::class];
    }

    public function performAction(): PurchaseInvoicePosition
    {
        $purchaseInvoicePosition = PurchaseInvoicePosition::query()
            ->whereKey($this->data['id'])
            ->first();

        $purchaseInvoicePosition->fill($this->data);
        $purchaseInvoicePosition->save();

        return $purchaseInvoicePosition->withoutRelations()->fresh();
    }
}
