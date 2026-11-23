<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\BlockedFile\BlockFile;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\BlockedFile;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rulesets\PurchaseInvoice\BlockPurchaseInvoiceFileRuleset;
use Illuminate\Validation\ValidationException;

class BlockPurchaseInvoiceFile extends FluxAction
{
    public static function models(): array
    {
        return [BlockedFile::class, PurchaseInvoice::class];
    }

    protected function getRulesets(): string|array
    {
        return BlockPurchaseInvoiceFileRuleset::class;
    }

    public function performAction(): bool
    {
        $purchaseInvoice = resolve_static(PurchaseInvoice::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['id', 'media_id']);

        BlockFile::make(['media_id' => $purchaseInvoice->media_id])
            ->validate()
            ->execute();

        $purchaseInvoice->purchaseInvoicePositions()->delete();

        return $purchaseInvoice->forceDelete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $purchaseInvoice = resolve_static(PurchaseInvoice::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['id', 'media_id', 'order_id']);

        $errors = [];

        if (! is_null($purchaseInvoice->order_id)) {
            $errors[] = 'The purchase invoice already has an order, its file cannot be blocked.';
        }

        if (is_null($purchaseInvoice->media_id)) {
            $errors[] = 'The purchase invoice has no attached document.';
        }

        if ($errors) {
            throw ValidationException::withMessages(['id' => $errors])
                ->errorBag('blockPurchaseInvoiceFile');
        }
    }
}
