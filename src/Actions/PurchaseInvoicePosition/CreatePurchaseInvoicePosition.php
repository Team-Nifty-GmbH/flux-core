<?php

namespace FluxErp\Actions\PurchaseInvoicePosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Rulesets\PurchaseInvoicePosition\CreatePurchaseInvoicePositionRuleset;

class CreatePurchaseInvoicePosition extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreatePurchaseInvoicePositionRuleset::class, 'getRules');
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
