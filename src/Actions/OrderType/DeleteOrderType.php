<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderType;

class DeleteOrderType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:order_types,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [OrderType::class];
    }

    public function performAction(): ?bool
    {
        return OrderType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
