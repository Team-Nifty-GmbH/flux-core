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
        $totalPrice = $this->getData('total_price');
        $amount = $this->getData('amount');

        if (is_null($this->getData('unit_price'))
            && is_numeric($totalPrice)
            && is_numeric($amount)
            && bccomp($amount, 0) !== 0
        ) {
            data_set(
                $this->data,
                'unit_price',
                bcdiv($totalPrice, $amount)
            );
        }
    }
}
