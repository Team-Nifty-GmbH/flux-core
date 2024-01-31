<?php

namespace FluxErp\Actions\PaymentReminder;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdatePaymentReminderRequest;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdatePaymentReminder extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdatePaymentReminderRequest())->rules();
    }

    public static function name(): string
    {
        return CreatePaymentReminder::name();
    }

    public static function models(): array
    {
        return [PaymentReminder::class];
    }

    public function performAction(): Model
    {
        $paymentReminder = PaymentReminder::query()
            ->whereKey($this->data['id'])
            ->first();

        $paymentReminder->fill($this->data);
        $paymentReminder->save();

        return $paymentReminder->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $paymentReminder = PaymentReminder::query()
            ->whereKey($this->data['id'])
            ->first(['id', 'order_id']);

        if (! Media::query()
            ->whereKey($this->data['media_id'])
            ->where('model_type', Order::class)
            ->where('model_id', $paymentReminder->order_id)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'media_id' => [__('validation.exists', ['attribute' => 'media_id'])],
            ])->errorBag('updatePaymentReminder');
        }
    }
}
