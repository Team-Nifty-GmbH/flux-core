<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rulesets\PurchaseInvoice\DeletePurchaseInvoiceRuleset;

class DeletePurchaseInvoice extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeletePurchaseInvoiceRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PurchaseInvoice::class];
    }

    public function performAction(): ?bool
    {
        return app(PurchaseInvoice::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
