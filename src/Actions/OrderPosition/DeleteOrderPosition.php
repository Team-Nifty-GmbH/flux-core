<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderPosition;
use FluxErp\Rulesets\OrderPosition\DeleteOrderPositionRuleset;

class DeleteOrderPosition extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteOrderPositionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [OrderPosition::class];
    }

    public function performAction(): ?bool
    {
        $orderPosition = app(OrderPosition::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $orderPosition->children()->delete();

        return $orderPosition->delete();
    }
}
