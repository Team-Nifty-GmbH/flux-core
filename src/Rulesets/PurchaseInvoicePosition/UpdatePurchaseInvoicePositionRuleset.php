<?php

namespace FluxErp\Rulesets\PurchaseInvoicePosition;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Product;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdatePurchaseInvoicePositionRuleset extends FluxRuleset
{
    protected static ?string $model = PurchaseInvoicePosition::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PurchaseInvoicePosition::class]),
            ],
            'ledger_account_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'product_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'vat_rate_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => VatRate::class]),
            ],
            'name' => 'nullable|string|max:255',
            'amount' => app(Numeric::class, ['min' => 0]),
            'unit_price' => app(Numeric::class, ['min' => 0]),
            'total_price' => app(Numeric::class, ['min' => 0]),
        ];
    }
}
