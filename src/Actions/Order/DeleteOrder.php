<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Rulesets\Order\DeleteOrderRuleset;
use Illuminate\Validation\ValidationException;

class DeleteOrder extends FluxAction
{
    public static function models(): array
    {
        return [Order::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteOrderRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Order::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if ($order->is_locked) {
            $errors += [
                'is_locked' => ['Order is locked'],
            ];
        }

        if ($order->children()->count() > 0) {
            $errors += [
                'children' => ['Order has children'],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('deleteOrder')
                ->status(423);
        }
    }
}
