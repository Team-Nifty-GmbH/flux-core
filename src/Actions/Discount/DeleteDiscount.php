<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Discount;

class DeleteDiscount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:discounts,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Discount::class];
    }

    public function performAction(): ?bool
    {
        return Discount::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
