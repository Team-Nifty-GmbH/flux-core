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
            ->first();

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

        $error = match (true) {
            ! is_null($purchaseInvoice->order_id) => __(
                'The purchase invoice already has an order, its file cannot be blocked.'
            ),
            is_null($purchaseInvoice->media_id) => __('The purchase invoice has no attached document.'),
            default => null,
        };

        if ($error) {
            throw ValidationException::withMessages(['id' => [$error]])
                ->errorBag('blockPurchaseInvoiceFile');
        }
    }
}
