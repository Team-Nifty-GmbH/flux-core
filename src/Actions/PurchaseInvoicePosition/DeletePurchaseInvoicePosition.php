<?php

namespace FluxErp\Actions\PurchaseInvoicePosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Rulesets\PurchaseInvoicePosition\DeletePurchaseInvoicePositionRuleset;

class DeletePurchaseInvoicePosition extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeletePurchaseInvoicePositionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PurchaseInvoicePosition::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(PurchaseInvoicePosition::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
