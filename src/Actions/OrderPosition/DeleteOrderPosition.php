<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\OrderPosition;

class DeleteOrderPosition extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:order_positions,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [OrderPosition::class];
    }

    public function execute(): ?bool
    {
        $orderPosition = OrderPosition::query()
            ->whereKey($this->data['id'])
            ->first();

        $orderPosition->children()->delete();

        return $orderPosition->delete();
    }
}
