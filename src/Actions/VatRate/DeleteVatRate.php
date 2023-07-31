<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\VatRate;

class DeleteVatRate extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:vat_rates,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function execute(): ?bool
    {
        return VatRate::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
