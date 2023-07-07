<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\OrderType;

class DeleteOrderType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:order_types,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [OrderType::class];
    }

    public function execute(): bool|null
    {
        return OrderType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
