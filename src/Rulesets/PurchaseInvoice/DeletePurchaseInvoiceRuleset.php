<?php

namespace FluxErp\Rulesets\PurchaseInvoice;

use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePurchaseInvoiceRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = PurchaseInvoice::class;

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
