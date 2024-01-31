<?php

namespace FluxErp\Actions\Payment;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreatePaymentRunRequest;
use FluxErp\Models\PaymentRun;
use Illuminate\Support\Arr;

class CreatePaymentRun extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreatePaymentRunRequest())->rules();
    }

    public static function models(): array
    {
        return [PaymentRun::class];
    }

    public function performAction(): mixed
    {
        $orders = Arr::pull($this->data, 'orders');

        $payment = new PaymentRun($this->data);
        $payment->save();

        $payment->orders()->sync($orders);

        return $payment->fresh();
    }
}
