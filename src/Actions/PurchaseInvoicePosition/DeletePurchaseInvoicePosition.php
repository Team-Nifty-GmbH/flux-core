<?php

namespace FluxErp\Actions\PurchaseInvoicePosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Rulesets\PurchaseInvoicePosition\DeletePurchaseInvoicePositionRuleset;

class DeletePurchaseInvoicePosition extends FluxAction
{
    public static function models(): array
    {
        return [PurchaseInvoicePosition::class];
    }

    protected function getRulesets(): string|array
    {
        return DeletePurchaseInvoicePositionRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(PurchaseInvoicePosition::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
