<?php

namespace FluxErp\Rulesets\PurchaseInvoicePosition;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Product;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rules\Sole;
use FluxErp\Rulesets\FluxRuleset;

class CreatePurchaseInvoicePositionRuleset extends FluxRuleset
{
    protected static ?string $model = PurchaseInvoicePosition::class;

    public function rules(): array
    {
        return [
            'uuid' => [
                'string',
                app(Sole::class, ['model' => PurchaseInvoicePosition::class]),
            ],
            'purchase_invoice_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PurchaseInvoice::class]),
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
            'amount' => [
                'required',
                app(Numeric::class, ['min' => 0]),
            ],
            'unit_price' => [
                'required',
                app(Numeric::class, ['min' => 0]),
            ],
            'total_price' => [
                'required',
                app(Numeric::class, ['min' => 0]),
            ],
        ];
    }
}
