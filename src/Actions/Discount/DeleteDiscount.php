<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Discount;

class DeleteDiscount extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:discounts,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Discount::class];
    }

    public function execute(): bool|null
    {
        return Discount::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
