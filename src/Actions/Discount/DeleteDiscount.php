<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Discount;
use FluxErp\Rulesets\Discount\DeleteDiscountRuleset;

class DeleteDiscount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteDiscountRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Discount::class];
    }

    public function performAction(): ?bool
    {
        return app(Discount::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
