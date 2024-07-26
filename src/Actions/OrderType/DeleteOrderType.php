<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\OrderType\DeleteOrderTypeRuleset;

class DeleteOrderType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteOrderTypeRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [OrderType::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(OrderType::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
