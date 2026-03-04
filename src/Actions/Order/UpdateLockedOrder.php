<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentType;
use FluxErp\Rulesets\Order\UpdateLockedOrderRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

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

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $tenantId = null;
        if ($paymentTypeId = $this->getData('payment_type_id')) {
            $tenantId ??= resolve_static(Order::class, 'query')
                ->whereKey($this->getData('id'))
                ->value('tenant_id');
            if (resolve_static(PaymentType::class, 'query')
                ->whereKey($paymentTypeId)
                ->whereHasTenant($tenantId)
                ->doesntExist()
            ) {
                $errors += [
                    'payment_type_id' => ['Payment Type not found on given tenant.'],
                ];
            }
        }

        if ($addresses = $this->getData('addresses')) {
            $tenantId ??= resolve_static(Order::class, 'query')
                ->whereKey($this->getData('id'))
                ->value('tenant_id');
            foreach ($addresses as $key => $address) {
                if (resolve_static(Address::class, 'query')
                    ->whereKey($address['address_id'])
                    ->whereHasTenant($tenantId)
                    ->doesntExist()
                ) {
                    $errors += [
                        'addresses.' . $key . '.address_id' => ['Address not found on given tenant.'],
                    ];
                }

                if (resolve_static(AddressType::class, 'query')
                    ->whereKey($address['address_type_id'])
                    ->whereHasTenant($tenantId)
                    ->doesntExist()
                ) {
                    $errors += [
                        'addresses.' . $key . '.address_type_id' => ['Address Type not found on given tenant.'],
                    ];
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('updateLockedOrder');
        }
    }
}
