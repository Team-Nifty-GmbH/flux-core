<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderPosition;
use FluxErp\Rulesets\OrderPosition\DeleteOrderPositionRuleset;
use Illuminate\Validation\ValidationException;

class DeleteOrderPosition extends FluxAction
{
    public static function models(): array
    {
        return [OrderPosition::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteOrderPositionRuleset::class;
    }

    public function performAction(): ?bool
    {
        $orderPosition = resolve_static(OrderPosition::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

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
                'is_locked' => ['Order is locked'],
            ];
        }

        if ($orderPosition->is_bundle_position) {
            $errors += [
                'is_bundle_position' => ['You cannot delete a bundle position.'],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('deleteOrderPosition');
        }
    }
}
