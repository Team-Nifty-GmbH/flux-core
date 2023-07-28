<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\VatRate;

class DeleteVatRate extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:vat_rates,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function performAction(): ?bool
    {
        return VatRate::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
