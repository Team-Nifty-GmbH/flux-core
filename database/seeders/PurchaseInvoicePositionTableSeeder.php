<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Product;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\VatRate;
use Illuminate\Database\Seeder;

class PurchaseInvoicePositionTableSeeder extends Seeder
{
    public function run(): void
    {
        $purchaseInvoiceIds = PurchaseInvoice::query()->get('id');
        $cutPurchaseInvoiceIds = $purchaseInvoiceIds->random(bcfloor($purchaseInvoiceIds->count() * 0.7));

        $ledgerAccountIds = LedgerAccount::query()->get('id');
        $cutLedgerAccountIds = $ledgerAccountIds->random(bcfloor($ledgerAccountIds->count() * 0.7));

        $productIds = Product::query()->get('id');
        $cutProductIds = $productIds->random(bcfloor($productIds->count() * 0.7));

        $vatRateIds = VatRate::query()->get('id');
        $cutVatRateIds = $vatRateIds->random(bcfloor($vatRateIds->count() * 0.7));

        PurchaseInvoicePosition::factory()->count(10)->create([
            'purchase_invoice_id' => fn () => $cutPurchaseInvoiceIds->random()->getKey(),
            'ledger_account_id' => fn () => faker()->boolean(50) ? $cutLedgerAccountIds->random()->getKey() : null,
            'product_id' => fn () => faker()->boolean(50) ? $cutProductIds->random()->getKey() : null,
            'vat_rate_id' => fn () => faker()->boolean(75) ? $cutVatRateIds->random()->getKey() : null,
        ]);
    }
}
