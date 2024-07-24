<?php

namespace FluxErp\Actions\PurchaseInvoicePosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Rulesets\PurchaseInvoicePosition\UpdatePurchaseInvoicePositionRuleset;

class UpdatePurchaseInvoicePosition extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdatePurchaseInvoicePositionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PurchaseInvoicePosition::class];
    }

    public function performAction(): PurchaseInvoicePosition
    {
        $purchaseInvoicePosition = resolve_static(PurchaseInvoicePosition::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $purchaseInvoicePosition->fill($this->data);
        $purchaseInvoicePosition->save();

        return $purchaseInvoicePosition->withoutRelations()->fresh();
    }
}
