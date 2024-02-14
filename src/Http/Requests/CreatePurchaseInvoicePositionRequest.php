<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Product;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;

class CreatePurchaseInvoicePositionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:purchase_invoice_positions,uuid',
            'purchase_invoice_id' => [
                'required',
                'integer',
                new ModelExists(PurchaseInvoice::class),
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
            'amount' => [
                'required',
                new Numeric(min: 0),
            ],
            'unit_price' => [
                'required',
                new Numeric(min: 0),
            ],
            'total_price' => [
                'required',
                new Numeric(min: 0),
            ],
        ];
    }
}
