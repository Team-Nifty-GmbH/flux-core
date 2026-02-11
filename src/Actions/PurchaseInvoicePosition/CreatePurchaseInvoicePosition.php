<?php

namespace FluxErp\Actions\PurchaseInvoicePosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Rulesets\PurchaseInvoicePosition\CreatePurchaseInvoicePositionRuleset;

class CreatePurchaseInvoicePosition extends FluxAction
{
    public static function models(): array
    {
        return [PurchaseInvoicePosition::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePurchaseInvoicePositionRuleset::class;
    }

    public function performAction(): PurchaseInvoicePosition
    {
        $purchaseInvoicePosition = app(PurchaseInvoicePosition::class, ['attributes' => $this->data]);
        $purchaseInvoicePosition->save();

        return $purchaseInvoicePosition->fresh();
    }

    protected function prepareForValidation(): void
    {
        if (! $this->getData('unit_price')
            && $this->getData('total_price')
            && $this->getData('amount')
        ) {
            data_set(
                $this->data,
                'unit_price',
                bcdiv(
                    $this->getData('total_price'),
                    $this->getData('amount')
                )
            );
        }
    }
}
