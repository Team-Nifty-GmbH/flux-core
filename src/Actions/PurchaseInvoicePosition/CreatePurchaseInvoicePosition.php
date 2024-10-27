<?php

namespace FluxErp\Actions\PurchaseInvoicePosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Rulesets\PurchaseInvoicePosition\CreatePurchaseInvoicePositionRuleset;

class CreatePurchaseInvoicePosition extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return CreatePurchaseInvoicePositionRuleset::class;
    }

    public static function models(): array
    {
        return [PurchaseInvoicePosition::class];
    }

    public function performAction(): PurchaseInvoicePosition
    {
        $purchaseInvoicePosition = app(PurchaseInvoicePosition::class, ['attributes' => $this->data]);
        $purchaseInvoicePosition->save();

        return $purchaseInvoicePosition->fresh();
    }
}
