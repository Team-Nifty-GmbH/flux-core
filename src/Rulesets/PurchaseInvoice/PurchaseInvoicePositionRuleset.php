<?php

namespace FluxErp\Rulesets\PurchaseInvoice;

use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rulesets\PurchaseInvoicePosition\CreatePurchaseInvoicePositionRuleset;
use Illuminate\Support\Arr;

class PurchaseInvoicePositionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return array_merge(
            [
                'purchase_invoice_positions' => 'array',
            ],
            Arr::prependKeysWith(
                Arr::except(
                    resolve_static(CreatePurchaseInvoicePositionRuleset::class, 'getRules'),
                    'purchase_invoice_id'
                ),
                'purchase_invoice_positions.*.'
            ),
        );
    }
}
