<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Product;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;

class CreatePurchaseInvoicePositionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:purchase_invoice_positions,uuid',
            'ledger_account_id' => [
                'nullable',
                'integer',
                new ModelExists(LedgerAccount::class),
            ],
            'purchase_invoice_id' => [
                'nullable',
                'integer',
                new ModelExists(PurchaseInvoice::class),
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
            'amount' => 'required|numeric',
            'unit_price' => 'required|numeric',
            'total_price' => 'required|numeric',
        ];
    }
}
