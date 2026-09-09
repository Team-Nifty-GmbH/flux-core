<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\OrderType\DeleteOrderTypeRuleset;
use Illuminate\Validation\ValidationException;

class DeleteOrderType extends FluxAction
{
    public static function models(): array
    {
        return [OrderType::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteOrderTypeRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(OrderType::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $orderType = resolve_static(OrderType::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        // A live order would lose the type it was created with once the type is
        // deleted, so the type has to be merged into another one first. Soft
        // deleted orders don't block it; restoring such an order restores its
        // type too.
        if ($orderType->orders()->exists()) {
            throw ValidationException::withMessages([
                'order' => ['Order type referenced by an order'],
            ])
                ->errorBag('deleteOrderType')
                ->status(423);
        }
    }
}
