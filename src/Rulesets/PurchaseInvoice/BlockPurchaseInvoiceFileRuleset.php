<?php

namespace FluxErp\Rulesets\PurchaseInvoice;

use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class BlockPurchaseInvoiceFileRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PurchaseInvoice::class]),
            ],
        ];
    }
}
