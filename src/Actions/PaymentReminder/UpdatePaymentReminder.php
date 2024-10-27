<?php

namespace FluxErp\Actions\PaymentReminder;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rulesets\PaymentReminder\UpdatePaymentReminderRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdatePaymentReminder extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdatePaymentReminderRuleset::class;
    }

    public static function name(): string
    {
        return resolve_static(CreatePaymentReminder::class, 'name');
    }

    public static function models(): array
    {
        return [PaymentReminder::class];
    }

    public function performAction(): Model
    {
        $paymentReminder = resolve_static(PaymentReminder::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $paymentReminder->fill($this->data);
        $paymentReminder->save();

        return $paymentReminder->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $paymentReminder = resolve_static(PaymentReminder::class, 'query')
            ->whereKey($this->data['id'])
            ->first(['id', 'order_id']);

        if (! resolve_static(Media::class, 'query')
            ->whereKey($this->data['media_id'])
            ->where('model_type', app(Order::class)->getMorphClass())
            ->where('model_id', $paymentReminder->order_id)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'media_id' => [__('validation.exists', ['attribute' => 'media_id'])],
            ])->errorBag('updatePaymentReminder');
        }
    }
}
