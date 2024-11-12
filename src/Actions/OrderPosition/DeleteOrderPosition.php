<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderPosition;
use FluxErp\Rulesets\OrderPosition\DeleteOrderPositionRuleset;
use Illuminate\Validation\ValidationException;

class DeleteOrderPosition extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteOrderPositionRuleset::class;
    }

    public static function models(): array
    {
        return [OrderPosition::class];
    }

    public function performAction(): ?bool
    {
        $orderPosition = resolve_static(OrderPosition::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $orderPosition->children()->delete();

        return $orderPosition->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $orderPosition = resolve_static(OrderPosition::class, 'query')
            ->whereKey($this->data['id'])
            ->with('order:id,is_locked')
            ->first();

        if ($orderPosition->order->is_locked) {
            $errors += [
                'is_locked' => [__('Order is locked')],
            ];
        }

        if ($orderPosition->is_bundle_position) {
            $errors += [
                'is_bundle_position' => [__('You cannot delete a bundle position.')],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('deleteOrderPosition');
        }
    }
}
