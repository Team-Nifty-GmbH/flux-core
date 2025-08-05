<?php

namespace FluxErp\Rulesets\PurchaseInvoicePosition;

use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePurchaseInvoicePositionRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = PurchaseInvoicePosition::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PurchaseInvoicePosition::class]),
            ],
        ];
    }
}
