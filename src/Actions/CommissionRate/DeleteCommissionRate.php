<?php

namespace FluxErp\Actions\CommissionRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CommissionRate;

class DeleteCommissionRate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:commission_rates,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [CommissionRate::class];
    }

    public function performAction(): ?bool
    {
        return CommissionRate::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
