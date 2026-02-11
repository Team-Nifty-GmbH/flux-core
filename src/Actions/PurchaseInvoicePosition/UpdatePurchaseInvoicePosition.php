<?php

namespace FluxErp\Actions\PurchaseInvoicePosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Rulesets\PurchaseInvoicePosition\UpdatePurchaseInvoicePositionRuleset;

class UpdatePurchaseInvoicePosition extends FluxAction
{
    public static function models(): array
    {
        return [PurchaseInvoicePosition::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdatePurchaseInvoicePositionRuleset::class;
    }

    public function performAction(): PurchaseInvoicePosition
    {
        $purchaseInvoicePosition = resolve_static(PurchaseInvoicePosition::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $purchaseInvoicePosition->fill($this->data);
        $purchaseInvoicePosition->save();

        return $purchaseInvoicePosition->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        if (($this->getData('total_price') || $this->getData('amount'))
            && ! $this->getData('unit_price')
        ) {
            $position = resolve_static(PurchaseInvoicePosition::class, 'query')
                ->whereKey($this->getData('id'))
                ->first();

            if ($position) {
                $totalPrice = $this->getData('total_price') ?? $position->total_price;
                $amount = $this->getData('amount') ?? $position->amount;

                if (bccomp($amount, 0) !== 0) {
                    data_set(
                        $this->data,
                        'unit_price',
                        bcdiv(
                            $totalPrice,
                            $amount
                        )
                    );
                }
            }
        }
    }
}
