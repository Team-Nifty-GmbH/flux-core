<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Product;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;

class UpdatePurchaseInvoicePositionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(PurchaseInvoicePosition::class),
            ],
            'ledger_account_id' => [
                'nullable',
                'integer',
                new ModelExists(LedgerAccount::class),
            ],
            'product_id' => [
                'nullable',
                'integer',
                new ModelExists(Product::class),
            ],
            'vat_rate_id' => [
                'nullable',
                'integer',
                new ModelExists(VatRate::class),
            ],
            'name' => 'nullable|string',
            'amount' => new Numeric(min: 0),
            'unit_price' => new Numeric(min: 0),
            'total_price' => new Numeric(min: 0),
        ];
    }
}
