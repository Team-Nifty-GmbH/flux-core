<?php

namespace FluxErp\Actions\PaymentReminder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rulesets\PaymentReminder\CreatePaymentReminderRuleset;
use Illuminate\Validation\ValidationException;

class CreatePaymentReminder extends FluxAction
{
    public static function models(): array
    {
        return [PaymentReminder::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePaymentReminderRuleset::class;
    }

    public function performAction(): PaymentReminder
    {
        $paymentReminder = app(PaymentReminder::class, ['attributes' => $this->data]);
        $paymentReminder->save();

        return $paymentReminder->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        // Validate Order
        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->data['order_id'])
            ->with('orderType:id,order_type_enum')
            ->first();

        if (! $order->invoice_number
            || ! $order->is_locked
            || $order->orderType->order_type_enum->multiplier() == -1
            || $order->orderType->order_type_enum->isPurchase()
        ) {
            throw ValidationException::withMessages([
                'order_id' => ['Unable to create payment reminder for given order.'],
            ])->errorBag('updatePaymentReminder');
        }

        // Validate Media
        if (($this->data['media_id'] ?? false)
            && ! resolve_static(Media::class, 'query')
                ->whereKey($this->data['media_id'])
                ->where('model_type', app(Order::class)->getMorphClass())
                ->where('model_id', $this->data['order_id'])
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'media_id' => [__('validation.exists', ['attribute' => 'media_id'])],
            ])->errorBag('updatePaymentReminder');
        }
    }
}
