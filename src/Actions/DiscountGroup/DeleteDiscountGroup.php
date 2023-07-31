<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DiscountGroup;

class DeleteDiscountGroup extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:discount_groups,id',
        ];
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function performAction(): ?bool
    {
        return DiscountGroup::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
