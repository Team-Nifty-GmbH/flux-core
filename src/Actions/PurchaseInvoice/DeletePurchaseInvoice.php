<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rulesets\PurchaseInvoice\DeletePurchaseInvoiceRuleset;

class DeletePurchaseInvoice extends FluxAction
{
    public static function models(): array
    {
        return [PurchaseInvoice::class];
    }

    protected function getRulesets(): string|array
    {
        return DeletePurchaseInvoiceRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(PurchaseInvoice::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
