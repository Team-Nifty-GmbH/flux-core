<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\DiscountGroup;

class DeleteDiscountGroup extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:discount_groups,id',
        ];
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function execute(): ?bool
    {
        return DiscountGroup::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
