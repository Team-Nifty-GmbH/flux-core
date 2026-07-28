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
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $orderType = resolve_static(OrderType::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        // An order keeps pointing at its type for the rest of its life, and the
        // type is hidden from every query once it is deleted. The order would
        // lose the type it was created with, so the type has to be merged into
        // another one first.
        if ($orderType->orders()->withTrashed()->exists()) {
            throw ValidationException::withMessages([
                'order' => ['Order type referenced by an order'],
            ])
                ->errorBag('deleteOrderType')
                ->status(423);
        }
    }
}
