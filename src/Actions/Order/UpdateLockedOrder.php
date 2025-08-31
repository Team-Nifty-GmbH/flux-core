<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Rulesets\Order\UpdateLockedOrderRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateLockedOrder extends FluxAction
{
    public static function description(): ?string
    {
        return 'Update an order regardless of its locked state.';
    }

    public static function models(): array
    {
        return [Order::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLockedOrderRuleset::class;
    }

    public function performAction(): Model
    {
        $addresses = Arr::pull($this->data, 'addresses');
        $users = Arr::pull($this->data, 'users');

        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $approvalUserId = $this->getData('approval_user_id', $order->approval_user_id);
        if ($approvalUserId !== $order->approval_user_id) {
            $order->approvalUser?->unsubscribeNotificationChannel($order->broadcastChannel());
        }

        $order->fill($this->data);
        $order->save();

        if (! is_null($addresses)) {
            $addresses = collect($addresses)
                ->unique(fn ($address) => $address['address_id'] . '_' . $address['address_type_id'])
                ->keyBy('address_id')
                ->toArray();

            $order->addresses()->sync($addresses);
        }

        if (! is_null($users)) {
            $order->users()->sync($users);
        }

        return $order->withoutRelations()->fresh();
    }
}
