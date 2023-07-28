<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\OrderPosition;

class DeleteOrderPosition extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:order_positions,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [OrderPosition::class];
    }

    public function performAction(): ?bool
    {
        $orderPosition = OrderPosition::query()
            ->whereKey($this->data['id'])
            ->first();

        $orderPosition->children()->delete();

        return $orderPosition->delete();
    }
}
