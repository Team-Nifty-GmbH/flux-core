<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Rulesets\Order\DeleteOrderRuleset;
use Illuminate\Validation\ValidationException;

class DeleteOrder extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteOrderRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function performAction(): ?bool
    {
        return app(Order::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $order = app(Order::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        if ($order->is_locked) {
            $errors += [
                'is_locked' => [__('Order is locked')],
            ];
        }

        if ($order->children()->count() > 0) {
            $errors += [
                'children' => [__('Order has children')],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('deleteOrder');
        }
    }
}
